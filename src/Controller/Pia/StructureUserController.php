<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Controller\Pia;

use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation as Nelmio;
use PiaApi\Entity\Pia\Structure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as Swg;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class StructureUserController extends LayerRestController
{
    /**
     * Lists all users of a specific Structure.
     *
     * @Swg\Tag(name="StructureUser")
     *
     * @FOSRest\Get("/structures/{structureId}/users", requirements={"structureId"="\d+"})
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="structureId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Structure"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns all users of given Structure",
     *     @Swg\Schema(
     *         type="array",
     *         @Swg\Items(ref=@Nelmio\Model(type=User::class, groups={"Default"}))
     *     )
     * )
     *
     * @Security("is_granted('CAN_SHOW_STRUCTURE')")
     *
     * @return View
     */
    public function listAction(Request $request, $structureId)
    {
        $entity = $this->getRepository()->find($structureId);

        if ($entity === null) {
            return $this->view($entity, Response::HTTP_NOT_FOUND);
        }

        $this->canAccessResourceOr403($entity);

        // get users for this structure
        $users = [];

        foreach ($entity->getUsers() as $user) {
            $profile = $user->getProfile();
            array_push($users, [
                'id' => $profile->getId(),
                'firstName' => $profile->getFirstName(),
                'lastName' => $profile->getLastName(),
                'roles' => $profile->getRoles()
            ]);
        }

        return $this->view($users, Response::HTTP_OK);
    }

    protected function getEntityClass()
    {
        return Structure::class;
    }

    public function canAccessResourceOr403($resource): void
    {
        if (!$resource instanceof Structure) {
            throw new AccessDeniedHttpException();
        }

        if ($this->getSecurity()->isGranted('CAN_MANAGE_STRUCTURES')) {
            $can_access = true;
        } else {
            $can_access = false;
            $structures = array_merge(
                [$this->getUser()->getStructure()],
                $this->getUser()->getProfile()->getPortfolioStructures()
            );
            if (in_array($resource, $structures)) {
                $can_access = true;
            }
        }

        if ($resource !== null && !$can_access) {
            throw new AccessDeniedHttpException();
        }
    }
}
