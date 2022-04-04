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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
    public function notifyAskForProcessingEvaluation($processing, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $processing;
        $template = 'pia/Email/processing/ask_for_processing_evaluation%s.email.twig';
        $params = ['processing_name' => $name, 'processing_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to evaluator when a redactor ask for evaluating each page of a pia.
     */
    public function notifyAskForPiaEvaluation($pia, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $pia;
        $template = 'pia/Email/pia/ask_for_pia_evaluation%s.email.twig';
        $params = ['pia_name' => $name, 'pia_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to evaluator when a dpo emit opinion or observations on a processing.
     */
    public function notifyEmitOpinionOrObservations($pia, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $pia;
        $template = 'pia/Email/pia/emit_opinion_or_observations%s.email.twig';
        $params = ['pia_name' => $name, 'pia_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    #3

    /**
     * send email to redactor when evaluator's opinion on a processing is TO_CORRECT or IMPROVABLE.
     */
    public function notifyEmitEvaluatorEvaluation($processing, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $processing;
        $template = 'pia/Email/processing/emit_evaluator_evaluation%s.email.twig';
        $params = ['processing_name' => $name, 'processing_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to redactor when evaluator's opinion on a pia is TO_CORRECT or IMPROVABLE.
     */
    public function notifyEmitPiaEvaluatorEvaluation($pia, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $pia;
        $template = 'pia/Email/pia/emit_pia_evaluator_evaluation%s.email.twig';
        $params = ['processing_name' => $name, 'processing_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to redactor when a dpo emit observations on a pia.
     */
    public function notifyEmitObservations($pia, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $pia;
        $template = 'pia/Email/pia/emit_observations%s.email.twig';
        $params = ['pia_name' => $name, 'pia_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to dpo when an evaluator has evaluated all parts of pia and they are all acceptable.
     */
    public function notifySubmitPiaToDpo($pia, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $pia;
        $template = 'pia/Email/pia/submit_pia_to_dpo%s.email.twig';
        $params = ['pia_name' => $name, 'pia_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to data controller when a dpo emits any observations.
     */
    public function notifyDataController($processing, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $processing;
        $template = 'pia/Email/processing/emit_dpo_opinion%s.email.twig';
        $params = ['processing_name' => $name, 'processing_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to dpo when a data controller validate a pia.
     */
    public function notifyDataProtectionOfficer($pia, $recipientEmail, $recipientName)
    {
        list($name, $route, $routeAttr) = $pia;
        $template = 'pia/Email/pia/emit_data_controller_validation%s.email.twig';
        $params = ['pia_name' => $name, 'pia_url' => $this->getAbsoluteUrl($route, $routeAttr)];
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = [$recipientEmail => $recipientName];
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

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
