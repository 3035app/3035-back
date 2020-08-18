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
        $qbOwnedByUser = $this->createQueryBuilder('f');

        $qbOwnedByUser
            ->join('f.structure', 's')
            ->join('s.users', 'u')
            ->orWhere('upper(s.name) LIKE :name')
            ->orWhere('upper(f.name) LIKE :name AND f.name != \'root\'')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $user->getId())
            ->setParameter('name', "%$name%");

        $ownedByUserResults = $qbOwnedByUser->getQuery()->getResult();

        $qbOwnedThroughPortfolio = $this->createQueryBuilder('f');

        $qbOwnedThroughPortfolio
            ->join('f.structure', 's')
            ->join('s.portfolio', 'p')
            ->join('p.users', 'u')
            ->orWhere('upper(s.name) LIKE :name')
            ->orWhere('upper(f.name) LIKE :name AND f.name != \'root\'')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $user->getId())
            ->setParameter('name', "%$name%");

        $ownedThroughPortfolioResults = $qbOwnedThroughPortfolio->getQuery()->getResult();


        return array_merge($ownedByUserResults, $ownedThroughPortfolioResults);
    }
}
