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
use PiaApi\Entity\Pia\Folder;
use PiaApi\Entity\Oauth\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as Swg;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class FolderUserController extends LayerRestController
{
    /**
     * Lists all users of a specific Folder.
     *
     * @Swg\Tag(name="FolderUser")
     *
     * @FOSRest\Get("/folders/{folderId}/users", requirements={"folderId"="\d+"})
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="folderId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Folder"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns all users of given Folder",
     *     @Swg\Schema(
     *         type="array",
     *         @Swg\Items(ref=@Nelmio\Model(type=User::class, groups={"Default"}))
     *     )
     * )
     *
     * @Security("is_granted('CAN_SHOW_FOLDER')")
     *
     * @return View
     */
    public function listAction(Request $request, $folderId)
    {
        $folder = $this->getRepository()->find($folderId);
        if ($folder === null) {
            return $this->view($folder, Response::HTTP_NOT_FOUND);
        }
        $this->canAccessResourceOr403($folder);

        // get users assigned to this folder
        $users = $this->getObjectUsersAssigned($folder);
        return $this->view($users, Response::HTTP_OK);
    }

    /**
     * Attach a Folder to a user.
     *
     * @Swg\Tag(name="FolderUser")
     *
     * @FOSRest\Put("/folders/{folderId}/users/{userId}", requirements={"folderId"="\d+","userId"="\d+"})
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="folderId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Folder"
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
     *     description="Returns the updated Folder",
     *     @Swg\Schema(
     *         type="object",
     *         ref=@Nelmio\Model(type=Folder::class, groups={"Default"})
     *     )
     * )
     *
     * @Security("is_granted('CAN_ASSIGN_FOLDER_USER')")
     *
     * @return View
     */
    public function attachAction(Request $request, $folderId, $userId)
    {
        // get folder and user
        list($folder, $user) = $this->getResources($folderId, $userId);
        $this->canUpdateResourceOr403($folder);

        // propagate user's inheriting to children (folder, subfolders and processings)
        $folder->inheritUser($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->view($folder, Response::HTTP_OK);
    }

    /**
     * detach a Folder from a user.
     *
     * @Swg\Tag(name="FolderUser")
     *
     * @FOSRest\Delete("/folders/{folderId}/users/{userId}", requirements={"folderId"="\d+","userId"="\d+"})
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="folderId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Folder"
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
     * @Security("is_granted('CAN_REMOVE_FOLDER_USER')")
     *
     * @return View
     */
    public function detachAction(Request $request, $folderId, $userId)
    {
        // get folder and user
        list($folder, $user) = $this->getResources($folderId, $userId);
        $this->canDeleteResourceOr403($folder);

        // remove user's inheriting of children (folder, subfolders and processings)
        $folder->removeInheritUser($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->view([], Response::HTTP_OK);
    }

    protected function getResources($folderId, $userId)
    {
        // get folder
        $folder = $this->getResource($folderId);
        $this->canAccessResourceOr403($folder);
        // get user
        $user = $this->getUserResource($userId);
        return [$folder, $user];
    }

    protected function getEntityClass()
    {
        return Folder::class;
    }

    public function canAccessResourceOr403($resource): void
    {
        if (!$resource instanceof Folder) {
            throw new AccessDeniedHttpException('*');
        }

        // check that folder is in user's structure
        if ($resource->getStructure() !== $this->getUser()->getStructure()) {
            throw new AccessDeniedHttpException("the structure of folder is different from user's structure.");
        }
    }

    /**
     * Checks permissions while updating folder.
     * the error code sent is managed by front for translation.
     */
    public function canUpdateResourceOr403($resource): void
    {
        // prevent updating folder if no access to folder
        if (!$resource->canAccess($this->getUser()) && !$this->isGranted('CAN_ASSIGN_FOLDER_USER')) {
            // you are not allowed to update this folder.
            throw new AccessDeniedHttpException('messages.http.403.2');
        }
    }

    /**
     * Checks permissions while deleting folder.
     * the error code sent is managed by front for translation.
     */
    public function canDeleteResourceOr403($resource): void
    {
        // prevent deleting folder if no access to folder
        if (!$resource->canAccess($this->getUser()) && !$this->isGranted('CAN_REMOVE_FOLDER_USER')) {
            // you are not allowed to delete this folder.
            throw new AccessDeniedHttpException('messages.http.403.6');
        }
    }
}
