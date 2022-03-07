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
use PiaApi\Entity\Pia\Processing;
use PiaApi\Entity\Oauth\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as Swg;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProcessingUserController extends LayerRestController
{
    /**
     * Lists all users of a specific Processing.
     *
     * @Swg\Tag(name="ProcessingUser")
     *
     * @FOSRest\Get("/processings/{processingId}/users", requirements={"processingId"="\d+"})
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="processingId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Processing"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns all users of given Processing",
     *     @Swg\Schema(
     *         type="array",
     *         @Swg\Items(ref=@Nelmio\Model(type=User::class, groups={"Default"}))
     *     )
     * )
     *
     * @Security("is_granted('CAN_SHOW_PROCESSING')")
     * 
     * @return View
     */
    public function listAction(Request $request, $processingId)
    {
        // get processing
        $processing = $this->getResource($processingId);
        $this->canAccessResourceOr403($processing);

        // get users assigned to this processing
        $users = [];

        foreach ($processing->getUsers() as $user) {
            array_push($users, [
                'id' => $user->getId(),
                'firstName' => $user->getProfile()->getFirstName(),
                'lastName' => $user->getProfile()->getLastName(),
            ]);
        }

        return $this->view($users, Response::HTTP_OK);
    }

    /**
     * Attach a Processing to a user.
     *
     * @Swg\Tag(name="ProcessingUser")
     *
     * @FOSRest\Put("/processings/{processingId}/users/{userId}", requirements={"processingId"="\d+","userId"="\d+"})
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="processingId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Processing"
     * )
     * @Swg\Parameter(
     *     name="userId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the User"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns the updated Processing",
     *     @Swg\Schema(
     *         type="object",
     *         ref=@Nelmio\Model(type=Processing::class, groups={"Default"})
     *     )
     * )
     *
     * @Security("is_granted('CAN_EDIT_PROCESSING')")
     *
     * @return View
     */
    public function attachAction(Request $request, $processingId, $userId)
    {
        // get processing and user
        list($processing, $user) = $this->getResources($processingId, $userId);

        // attach user to processing
        $processing->addUser($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->view($processing, Response::HTTP_OK);
    }

    /**
     * detach a Processing from a user.
     *
     * @Swg\Tag(name="ProcessingUser")
     *
     * @FOSRest\Delete("/processings/{processingId}/users/{userId}", requirements={"processingId"="\d+","userId"="\d+"})
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="processingId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Processing"
     * )
     * @Swg\Parameter(
     *     name="userId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the User"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Empty content"
     * )
     *
     * @Security("is_granted('CAN_EDIT_PROCESSING')")
     *
     * @return View
     */
    public function detachAction(Request $request, $processingId, $userId)
    {
        // get processing and user
        list($processing, $user) = $this->getResources($processingId, $userId);

        // detach user from processing
        $processing->removeUser($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->view([], Response::HTTP_OK);
    }

    protected function getResources($processingId, $userId)
    {
        // get processing
        $processing = $this->getResource($processingId);
        $this->canAccessResourceOr403($processing);
        // get user
        $user = $this->getUserResource($userId);
        return [$processing, $user];
    }

    protected function getEntityClass()
    {
        return Processing::class;
    }

    public function canAccessResourceOr403($resource): void
    {
        if (!$resource instanceof Processing) {
            throw new AccessDeniedHttpException();
        }

        if ($this->getSecurity()->isGranted('CAN_MANAGE_PROCESSINGS')) {
            $can_access = true;
        } else {
            $can_access = false;
            foreach ($resource->getUsers() as $user) {
                if ($user === $this->getUser()) {
                    $can_access = true;
                    break;
                }
            }
        }

        if ($resource !== null && !$can_access) {
            throw new AccessDeniedHttpException();
        }
    }
}
