<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Controller\Pia;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation as Nelmio;
use PiaApi\Entity\Pia\UserProfile;
use PiaApi\Security\Role\RoleHierarchy;
use Swagger\Annotations as Swg;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProfileController extends RestController
{
    /**
     * @var RoleHierarchy
     */
    private $roleHierarchy;

    public function __construct(PropertyAccessorInterface $propertyAccessor, RoleHierarchy $roleHierarchy, SerializerInterface $serializer)
    {
        parent::__construct($propertyAccessor, $serializer);
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * Shows the current User's profile.
     *
     * @Swg\Tag(name="UserProfile")
     *
     * @FOSRest\Get("/profile")
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns the current User's profile",
     *     @Swg\Schema(
     *         type="array",
     *         @Swg\Items(ref=@Nelmio\Model(type=UserProfile::class, groups={"Default"}))
     *     )
     * )
     *
     * @return array
     */
    public function profileAction(UserInterface $user = null)
    {
        $this->canAccessRouteOr403();

        if (!$this->roleHierarchy->isGranted($user,"ROLE_CONTROLLER_MULTI") &&
            !$this->roleHierarchy->isGranted($user,"ROLE_SHARED_DPO") && !empty($user->getPortfolios())){
            $this->propertyAccessor->setValue($user, "portfolios", new ArrayCollection());
        }

        return $this->view($user->getProfile(), Response::HTTP_OK);
    }

    /**
     * Shows the current User's profile.
     *
     * @Swg\Tag(name="UserProfile")
     *
     * @FOSRest\Get("/profile/structures")
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns the current User's profile",
     *     @Swg\Schema(
     *         type="array",
     *         @Swg\Items(ref=@Nelmio\Model(type=UserProfile::class, groups={"Default"}))
     *     )
     * )
     *
     * @return array
     */
    public function profileStructuresAction(UserInterface $user = null)
    {
        return $this->profileAction($user);
    }

    protected function getEntityClass()
    {
        return UserProfile::class;
    }
}
