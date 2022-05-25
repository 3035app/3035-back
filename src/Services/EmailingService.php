<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Services;

use Doctrine\Common\Collections\ArrayCollection;
use PiaApi\Entity\Pia\Processing;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EmailingService
{
    protected $environment;
    protected $from;
    protected $frontUrl;
    protected $logger;
    protected $mailer;
    protected $router;
    protected $twig;

    public function __construct(
        \Twig_Environment $twig,
        LoggerInterface $logger,
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        TokenStorageInterface $tokenStorage,
        string $PialabEnvironment,
        array $PialabFromEmail
    ) {
        $this->environment = $PialabEnvironment;
        $this->from = $PialabFromEmail;
        $this->frontUrl = $this->getFrontUrl($tokenStorage);
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
    }

    /**
     * send email to users when they are assigning processing or pia.
     */
    public function notifyAssignProcessingAndPiaUsers($attributes, $recipient, $source)
    {
        list($name, $route, $routeAttr) = $attributes;
        $params = [];
        $params['object_name'] = $name;
        $params['source_name'] = $this->getSourceParameters($source);
        $params['source_url'] = $this->getAbsoluteUrl($route, $routeAttr);

        $template = 'pia/Email/assigning_processing_and_pia_user%s.email.twig';
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $this->sendEmail($subject, $body, $this->from, $this->getRecipient($recipient));
        return true;
    }

    /**
     * send email to evaluator when a redactor ask for evaluating a processing.
     */
    public function notifyAskForProcessingEvaluation($processingAttr, $recipient, $source)
    {
        $template = 'ask_for_processing_evaluation';
        list($subject, $body, $to) = $this->getEmailParameters($processingAttr, $recipient, $source, $template);
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to evaluator when a redactor ask for evaluating each page of a pia.
     */
    public function notifyAskForPiaEvaluation($piaAttr, $recipient, $source)
    {
        $template = 'ask_for_pia_evaluation';
        list($subject, $body, $to) = $this->getEmailParameters($piaAttr, $recipient, $source, $template);
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to evaluator when a dpo emit opinion or observations on a processing.
     */
    public function notifyEmitOpinionOrObservations($piaAttr, $recipient, $source)
    {
        $template = 'emit_opinion_or_observations';
        list($subject, $body, $to) = $this->getEmailParameters($piaAttr, $recipient, $source, $template);
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to redactor when evaluator's opinion on a processing is TO_CORRECT or IMPROVABLE.
     */
    public function notifyEmitEvaluatorEvaluation($processingAttr, $recipient, $source)
    {
        $template = 'emit_evaluator_evaluation';
        list($subject, $body, $to) = $this->getEmailParameters($processingAttr, $recipient, $source, $template);
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to redactor when evaluator's opinion on a pia is TO_CORRECT or IMPROVABLE.
     */
    public function notifyEmitPiaEvaluatorEvaluation($piaAttr, $recipient, $source)
    {
        $template = 'emit_pia_evaluator_evaluation';
        list($subject, $body, $to) = $this->getEmailParameters($piaAttr, $recipient, $source, $template);
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to redactor when a dpo emit observations on a pia.
     */
    public function notifyEmitObservations($piaAttr, $recipient, $source)
    {
        $template = 'emit_observations';
        list($subject, $body, $to) = $this->getEmailParameters($piaAttr, $recipient, $source, $template);
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to dpo when an evaluator has evaluated all parts of pia and they are all acceptable.
     */
    public function notifySubmitPiaToDpo($piaAttr, $recipient, $source)
    {
        $template = 'submit_pia_to_dpo';
        list($subject, $body, $to) = $this->getEmailParameters($piaAttr, $recipient, $source, $template);
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to data controller when a dpo emits any observations.
     */
    public function notifyDataController($processingAttr, $recipient, $source)
    {
        $template = 'emit_dpo_opinion';
        list($subject, $body, $to) = $this->getEmailParameters($processingAttr, $recipient, $source, $template);
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * send email to dpo when a data controller validate a pia.
     */
    public function notifyDataProtectionOfficer($piaAttr, $recipient, $source)
    {
        $template = 'emit_data_controller_validation';
        list($subject, $body, $to) = $this->getEmailParameters($piaAttr, $recipient, $source, $template);
        return $this->sendEmail($subject, $body, $this->from, $to);
    }

    /**
     * @param $subject string
     * @param $body string
     * @param $from array|string
     * @param $to array|string
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
            return 0;
        }

        // only 1 recipient
        return $this->mailer->send($email);
    }

    /**
     * @param $recipient User
     * @return array
     */
    private function getEmailParameters($objAttr, $recipient, $source, $tmpl)
    {
        $index = count($objAttr) - 1; // object is last item
        if ($objAttr[$index] instanceof Processing) {
            $params = $this->getProcessingParameters($objAttr, $source);
            $template = sprintf('pia/Email/processing/%s', $tmpl);
        } else {
            $params = $this->getPiaParameters($objAttr, $source);
            $template = sprintf('pia/Email/pia/%s', $tmpl);
        }
        $template .= '%s.email.twig';
        $subject = $this->twig->render(sprintf($template, '_subject'), $params);
        $body = $this->twig->render(sprintf($template, '_body'), $params);
        $to = $this->getRecipient($recipient);
        return [$subject, $body, $to];
    }

    /**
     * @return array
     */
    private function getProcessingParameters($attributes, $source)
    {
        list($name, $route, $routeAttr) = $attributes;
        $params = [];
        $params['processing_name'] = $name;
        $params['processing_url'] = $this->getAbsoluteUrl($route, $routeAttr);
        $params['source_name'] = $this->getSourceParameters($source);
        return $params;
    }

    /**
     * @return array
     */
    private function getPiaParameters($pia, $source)
    {
        list($name, $route, $routeAttr) = $pia;
        $params = [];
        $params['pia_name'] = $name;
        $params['pia_url'] = $this->getAbsoluteUrl($route, $routeAttr);
        $params['source_name'] = $this->getSourceParameters($source);
        return $params;
    }

    /**
     * @param $mixed User|array
     * @return array
     */
    private function getSourceParameters($mixed)
    {
        if (is_array($mixed) || $mixed instanceof \ArrayAccess) {
            $sources_names = [];
            foreach ($mixed as $source) {
                array_push($sources_names, $source->getProfile()->getFullname());
            }
            return implode(' ou ', $sources_names);
        } else {
            return $mixed->getProfile()->getFullname();
        }
    }

    /**
     * @param $recipient User
     * @return array
     */
    private function getRecipient($recipient)
    {
        return [$recipient->getEmail() => $recipient->getProfile()->getFullname()];
    }

    /**
     * @return bool
     */
    private function getAbsoluteUrl($route, $replace)
    {
        return $this->frontUrl . str_replace(array_keys($replace), $replace, $route);
    }

    /**
     * @return string
     */
    private function getFrontUrl($token): ?string
    {
        $user = null;
        // special case when logout
        if (null !== $token->getToken()) {
            $user = $token->getToken()->getUser();
        }
        if (null == $user) {
            throw new AccessDeniedHttpException('user must be authorized!');
        }
        $frontUrl = $user->getApplication()->getUrl(); // get front url from app
        if (null == $frontUrl) {
            throw new NotFoundHttpException('application url must have been defined before sending email!');
        }
        return $frontUrl;
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
