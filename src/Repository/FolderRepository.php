<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\Folder;

class FolderRepository extends NestedTreeRepository
{
    /**
     * @param string $name
     * @param User $user
     * @return Folder[]
     */
    public function findByNameSearch(string $name, User $user)
    {
        $queryBuilder = $this->createQueryBuilder('f');

        $queryBuilder
            ->join('f.structure', 's')
            ->join('s.portfolio', 'p')
            ->join('p.users', 'u')
            ->where('upper(f.name) LIKE :name')
            ->andWhere('u.id = :userId')
            ->andWhere('f.name != :root')
            ->orWhere('upper(s.name) LIKE :name AND f.name != :root')
            ->setParameter('userId', $user->getId())
            ->setParameter('root', "root")
            ->setParameter('name', "%$name%");

        return $queryBuilder->getQuery()->getResult();
    }
}
