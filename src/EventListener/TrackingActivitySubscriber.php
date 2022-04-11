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
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PiaApi\Entity\Pia\TrackingLog;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Security;

class TrackingActivitySubscriber implements EventSubscriber
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    // this method can only return the event names; you cannot define a
    // custom method name to execute when each event triggers
    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
        ];
    }

    // callback methods must be called exactly like the events they listen to;
    // they receive an argument of type LifecycleEventArgs, which gives you access
    // to both the entity object of the event and the entity manager itself
    public function preUpdate(LifecycleEventArgs $args): void
    {
        if (null !== $args->getObject()->getId())
        {
            $this->logActivity(TrackingLog::ACTIVITY_LAST_MODIFICATION, $args);
        } else {
            $this->logActivity(TrackingLog::ACTIVITY_CREATED, $args);
        }

    }

    private function logActivity(string $activity, LifecycleEventArgs $args): void
    {
        // get authenticated user and log activity
        $args->getObject()->logTrackingActivity(
            $this->security->getUser(),
            $activity,
            $entity,
            $event->getObjectManager()
        );
    }
}
