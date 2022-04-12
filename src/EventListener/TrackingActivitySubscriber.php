<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PiaApi\Entity\Pia\TrackingInterface;
use PiaApi\Entity\Pia\TrackingLog;
use Symfony\Component\Security\Core\Security;

class TrackingActivitySubscriber implements EventSubscriber
{
    private $manager;
    private $security;
    private $em;
    private $uow;

    public function __construct(EntityManagerInterface $manager, Security $security)
    {
        $this->manager = $manager;
        $this->security = $security;
    }

    // this method can only return the event names; you cannot define a
    // custom method name to execute when each event triggers
    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs  $args)
    {
        $this->em = $args->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();

        // only inserts!
        foreach ($this->uow->getScheduledEntityInsertions() as $keyEntity => $entity)
        {
            if (!$entity instanceof TrackingInterface) return;
            # FIXME check $entity->getEntityClass() and $entity->getId()
            $this->logActivity(TrackingLog::ACTIVITY_CREATED, $entity);
        }

        // only updates!
        foreach ($this->uow->getScheduledEntityUpdates() as $keyEntity => $entity)
        {
            if (!$entity instanceof TrackingInterface) return;

            # remove all old logs!
            $params = [
                'activity' => TrackingLog::ACTIVITY_LAST_UPDATE,
                'contentType' => $entity->getEntityClass(),
                'entityId' => $entity->getId()
            ];
            foreach ($this->manager->getRepository(TrackingLog::class)->findBy($params) as $trackingLog)
            {
                $this->manager->remove($trackingLog);
            }

            # add a new one!
            $this->logActivity(TrackingLog::ACTIVITY_LAST_UPDATE, $entity);
        }
    }

    private function logActivity(string $activity, $entity): void
    {
        // get authenticated user and log activity
        $trackingLog = $entity->logTrackingActivity(
            $this->security->getUser(),
            $activity,
            $entity,
        );
        $this->em->persist($trackingLog);
        $classMetadata = $this->em->getClassMetadata(get_class($trackingLog));
        $this->uow->computeChangeSet($classMetadata, $trackingLog);        
    }
}
