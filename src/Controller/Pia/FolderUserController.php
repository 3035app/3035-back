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
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation as Nelmio;
use PiaApi\Entity\Pia\Folder;
use PiaApi\Entity\Pia\Structure;
use PiaApi\Exception\Folder\NonEmptyFolderCannotBeDeletedException;
use PiaApi\Exception\Folder\RootFolderCannotBeDeletedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as Swg;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class FolderUserController extends RestController
{
    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        SerializerInterface $serializer
    ) {
        parent::__construct($propertyAccessor, $serializer);
    }

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
        $users = [];

        foreach ($folder->getUsers() as $user) {
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
        return Folder::class;
    }

    public function canAccessResourceOr403($resource): void
    {
        if (!$resource instanceof Folder) {
            throw new AccessDeniedHttpException();
        }

        $can_access = false;
        $can_access = true;
/*
        foreach ($resource->getUsers() as $user) {
            if ($user === $this->getUser()) {
                $can_access = true;
                break;
            }
        }
*/
        if ($resource !== null && !$can_access) {
            throw new AccessDeniedHttpException();
        }
    }
}
