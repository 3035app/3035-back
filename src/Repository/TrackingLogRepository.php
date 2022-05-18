<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Repository;

use PiaApi\Entity\Pia\TrackingLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TrackingLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrackingLog::class);
    }

    public function findTrackingsBy(array $options)
    {
        if (array_key_exists('contentType', $options) && array_key_exists('entityId', $options))
        {
            $qb = $this
                ->createQueryBuilder('tl')
                ->leftJoin('tl.owner', 'u')
                ->leftJoin('u.profile', 'p')
                ->select('tl.activity')
                ->addSelect("CONCAT(CONCAT(p.firstName, ' '), p.lastName) AS owner")
                ->addSelect('tl.date')
                ->andWhere('tl.contentType = :contentType')
                ->andWhere('tl.entityId = :entityId')
                ->setParameters($options)
                ->orderBy('tl.date', 'ASC')
                ;
            return $qb->getQuery()->getResult();
        }
        return [];
    }
}
