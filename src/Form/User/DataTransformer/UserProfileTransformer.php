<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Form\User\DataTransformer;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\DataTransformerInterface;
use PiaApi\Entity\Pia\UserProfile;

class UserProfileTransformer implements DataTransformerInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param UserProfile $profile
     *
     * @return array
     */
    public function transform($profile)
    {
        if ($profile instanceof UserProfile) {
            $user = $profile->getUser();
            $array = [
            'id'            => $profile->getId(),
            'user'          => $profile->getUser()->getId(),
            'firstName'     => $profile->getFirstName(),
            'lastName'      => $profile->getLastName(),
          ];

            return $array;
        }

        return $profile;
    }

    /**
     * @param string $value
     *
     * @return UserProfile
     */
    public function reverseTransform($value)
    {
        $profile = new UserProfile();

        $profileRepository = $this->doctrine->getRepository(UserProfile::class);

        if (isset($value['id'])) {
            $profile = $profileRepository->find($value['id']);
        }
        if (isset($value['firstName'])) {
            $profile->setFirstName($value['firstName']);
        }
        if (isset($value['lastName'])) {
            $profile->setLastName($value['lastName']);
        }

        return $profile;
    }
}
