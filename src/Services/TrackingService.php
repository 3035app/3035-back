<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Services;

use Doctrine\ORM\EntityManagerInterface;
use PiaApi\Entity\Pia\TrackingLog;
use Symfony\Component\Security\Core\Security;

class TrackingService
{
    private $manager;
    private $security;
    private $uow;

    public function __construct(EntityManagerInterface $manager, Security $security)
    {
        $this->manager = $manager;
        $this->security = $security;
        $this->uow = $this->manager->getUnitOfWork();
    }

    public function logActivityLastUpdate($entity): void
    {
        $this->removeAndLogActivity(TrackingLog::ACTIVITY_LAST_UPDATE, $entity);
    }

    public function logActivityEvaluationRequest($entity): void
    {
        $this->removeAndLogActivity(TrackingLog::ACTIVITY_EVALUATION_REQUEST, $entity);
    }

    public function logActivityEvaluation($entity): void
    {
        $this->removeAndLogActivity(TrackingLog::ACTIVITY_EVALUATION, $entity);
    }

    public function logActivityIssueRequest($entity): void
    {
        $this->removeAndLogActivity(TrackingLog::ACTIVITY_ISSUE_REQUEST, $entity);
    }

    public function logActivityNoticeRequest($entity): void
    {
        $this->removeAndLogActivity(TrackingLog::ACTIVITY_NOTICE_REQUEST, $entity);
    }

    public function logActivityValidationRequest($entity): void
    {
        $this->removeAndLogActivity(TrackingLog::ACTIVITY_VALIDATION_REQUEST, $entity);
    }

    public function logActivityValidated($entity): void
    {
        $this->removeTrackings(TrackingLog::ACTIVITY_REJECTED, $entity);
        $this->removeAndLogActivity(TrackingLog::ACTIVITY_VALIDATED, $entity);
    }

    public function logActivityRejected($entity): void
    {
        $this->removeTrackings(TrackingLog::ACTIVITY_VALIDATED, $entity);
        $this->removeAndLogActivity(TrackingLog::ACTIVITY_REJECTED, $entity);
    }

    public function logActivityArchivedProcessing($entity): void
    {
        $this->removeAndLogActivity(TrackingLog::ACTIVITY_ARCHIVED, $entity);
    }


    ///////////////////////////////////////////////////////////////////////////////////

    public function logActivity(string $activity, $entity): void
    {
        // get authenticated user and log activity
        $trackingLog = $entity->logTrackingActivity(
            $this->security->getUser(),
            $activity,
            $entity,
        );
        $this->manager->persist($trackingLog);
        $classMetadata = $this->manager->getClassMetadata(get_class($trackingLog));
        $this->uow->computeChangeSet($classMetadata, $trackingLog);
    }

    private function removeAndLogActivity(string $activity, $entity): void
    {
        # remove all old logs!
        $this->removeTrackings($activity, $entity);
        # add a new one!
        $this->logActivity($activity, $entity);
        $this->manager->flush(); // FIXME is it useful?
    }

    private function removeTrackings(string $activity, $entity): void
    {
        $params = [
            'activity' => $activity,
            'contentType' => $entity->getEntityClass(),
            'entityId' => $entity->getId()
        ];

        foreach ($this->manager->getRepository(TrackingLog::class)->findBy($params) as $trackingLog)
        {
            $this->manager->remove($trackingLog);
        }
    }
}
