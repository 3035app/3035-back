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
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use PiaApi\Entity\Pia\TrackingInterface;
use PiaApi\Entity\Pia\TrackingLog;
use PiaApi\Services\TrackingService;

class TrackingActivitySubscriber implements EventSubscriber
{
    private $tracking;
    private $em;
    private $uow;

    public function __construct(TrackingService $tracking)
    {
        $this->tracking = $tracking;
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
            # at this point the ID is defined!
            $this->tracking->logActivity(TrackingLog::ACTIVITY_CREATED, $entity);
        }

        // only updates!
        foreach ($this->uow->getScheduledEntityUpdates() as $keyEntity => $entity)
        {
            if (!$entity instanceof TrackingInterface) return;
            # add a new one!
            $this->tracking->logActivityLastUpdate($entity);
        }
    }
}
