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
        # remove all old logs!
        $this->removeTrackings(TrackingLog::ACTIVITY_LAST_UPDATE, $entity);
        # add a new one!
        $this->logActivity(TrackingLog::ACTIVITY_LAST_UPDATE, $entity);
        $this->manager->flush();
    }

    public function logActivityEvaluationRequest($entity): void
    {
        # remove all old logs!
        $this->removeTrackings(TrackingLog::ACTIVITY_EVALUATION_REQUEST, $entity);
        # add a new one!
        $this->logActivity(TrackingLog::ACTIVITY_EVALUATION_REQUEST, $entity);
        $this->manager->flush();
    }

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
