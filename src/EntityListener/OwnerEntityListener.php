<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use PiaApi\Entity\Pia\Processing;
use Symfony\Component\Security\Core\Security;

class OwnerEntityListener
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function preUpdate(Processing $processing, LifecycleEventArgs $event)
    {
        $entity = $event->getObject();
        // returns User object or null if not authenticated
        if (null === $entity->getOwner())
        {
            $entity->setOwner($this->security->getUser());
        }
    }
}
