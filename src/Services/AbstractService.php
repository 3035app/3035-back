<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Services;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;


abstract class AbstractService
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrineRegistry;

    public function __construct(ManagerRegistry $doctrineRegistry)
    {
        $this->doctrineRegistry = $doctrineRegistry;
    }

    /**
     * Gets the current service entity class.
     *
     * @return string
     */
    abstract public function getEntityClass(): string;

    /**
     * Gets the current entity repository.
     *
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->doctrineRegistry->getRepository($this->getEntityClass());
    }
}
