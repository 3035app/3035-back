<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Services;

use FOS\UserBundle\Mailer\MailerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailingService
{
    protected $environment;
    protected $from;
    protected $logger;
    protected $mailer;
    protected $router;
    protected $twig;

    public function __construct(
        \Twig_Environment $twig,
        LoggerInterface $logger,
        MailerInterface $mailer,
        UrlGeneratorInterface $router,
        string $PialabEnvironment,
        array $PialabFromEmail
    ) {
        $this->environment = $PialabEnvironment;
        $this->from = $PialabFromEmail;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
    }

    /**
     * send email to evaluator when a redactor ask for evaluating a processing.
     */
    public function notifyWhenAskingForProcessingEvaluation($processing, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $processing;
        $template = 'pia/Email/processing_evaluation%s.email.twig';
        $params = ['processing_name' => $name, 'processing_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to evaluator when a dpo emit opinion or observations on a processing.
     */
    public function notifyWhenEmittingOpinionOrObservations($processing, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $processing;
        $template = 'pia/Email/emitting_opinion_or_observations%s.email.twig';
        $params = ['processing_name' => $name, 'processing_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to evaluator when a redactor fill in a processing after being observed by dpo.
     */
    public function notifyWhenFillingInProcessingAfterBeingObserved($processing, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $processing;
        $template = 'pia/Email/filling_in_processing_after_being_observed%s.email.twig';
        $params = ['processing_name' => $name, 'processing_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    #4

    /**
     * send email to redactor when evaluator's opinion on a pia is TO_CORRECT or IMPROVABLE.
     */
    public function notifyWhenEmittingEvaluatorOpinion($processing, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $processing;
        $template = 'pia/Email/emitting_evaluator_opinion%s.email.twig';
        $params = ['processing_name' => $name, 'processing_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    #6
    #7

    /**
     * send email to dpo when an evaluator ask for evaluating a processing.
     */
    public function notifyWhenSubmittingPia($processing, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $processing;
        $template = 'pia/Email/wishing_submit_pia%s.email.twig';
        $params = ['processing_name' => $name, 'processing_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to dpo when an evaluator ask for evaluating a processing.
     */
    public function notifyWhenEmittingDPOOpinion($processing, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $processing;
        $template = 'pia/Email/emitting_dpo_opinion%s.email.twig';
        $params = ['processing_name' => $name, 'processing_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    #10

    /**
     * @param string       $subject
     * @param string       $body
     * @param array|string $from
     * @param array|string $to
     * @return int
     */
    private function sendEmail($subject, $body, $from, $to)
    {
        // test environment: add string TEST to subject
        if ($this->isTestEnvironment()) {
            $subject = '## TEST ##' . $subject;
        }

        $email = (new \Swift_Message())
            ->setSubject($subject)
            ->setBody($body, 'text/html')
            ->setFrom($from)
            ->setTo($to)
            ;

        // dev environment: log email
        if ($this->isDevEnvironment()) {
            $this->logger->info('Emailing to ' . json_encode($to) . ' : ' . $subject . "\n" . $body);
            return 0; // number of successful recipients reached
        }

        // number of successful recipients reached
        return $this->mailer->send($email);
    }

    /**
     * @return bool
     */
    private function getAbsoluteUrl($route, $params)
    {
        return $this->router->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @return bool
     */
    private function isTestEnvironment()
    {
        return 'test' == $this->environment;
    }

    /**
     * @return bool
     */
    private function isDevEnvironment()
    {
        return 'dev' == $this->environment;
    }
}
