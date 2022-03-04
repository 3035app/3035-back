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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as Swg;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProcessingUserController extends LayerRestController
{
    /**
     * Lists all Attachments for a specific Processing.
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
     */
    public function listAction(Request $request, $processingId)
    {
        $processing = $this->getRepository()->find($processingId);

        if ($processing === null) {
            return $this->view($processing, Response::HTTP_NOT_FOUND);
        }

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
