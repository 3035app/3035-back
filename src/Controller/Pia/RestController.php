<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Controller\Pia;

use Doctrine\Common\Util\Inflector as Inflector;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface;
use PiaApi\DataHandler\RequestDataHandler;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\Pia;
use PiaApi\Entity\Pia\Processing;
use PiaApi\Services\EmailingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

abstract class RestController extends AbstractFOSRestController
{
    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    private static $entityClasses = null;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(PropertyAccessorInterface $propertyAccessor, SerializerInterface $serializer)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->serializer = $serializer;
    }

    /**
     * Persists resource.
     *
     * @param mixed $entity
     */
    protected function persist($entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Updates resource.
     *
     * @param mixed $entity
     */
    protected function update($entity)
    {
        $this->getEntityManager()->merge($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Removes resource.
     *
     * @param mixed $entity
     */
    protected function remove($entity)
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Gets resource entity manager.
     *
     * @return EntityManager
     */
    protected function getEntityManager(): EntityManager
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * Gets current resource entity repository.
     *
     * @return EntityRepository
     */
    protected function getRepository(?string $entityClass = null): EntityRepository
    {
        return $this->getDoctrine()->getRepository($entityClass ?? $this->getEntityClass());
    }

    /**
     * Gives a resource for specific ID for the given class.
     *
     * @param int         $id
     * @param string|null $entityclass
     */
    protected function getResource(int $id, ?string $entityclass = null)
    {
        $repo = $this->getRepository();
        if ($entityclass !== null) {
            $repo = $this->getDoctrine()->getRepository($entityclass);
        }

        return $repo->find($id);
    }

    /**
     * Gives User resource for specific ID.
     *
     * @param int $id
     */
    protected function getUserResource(int $userId)
    {
        // get user
        $user = $this->getResource($userId, User::class);
        if ($user === null) {
            return $this->view($user, Response::HTTP_NOT_FOUND);
        }
        return $user;
    }

    /**
     * Merge a resource with its representation served in request.
     *
     * @param mixed   $entity
     * @param array   $attributesToMerge
     * @param Request $request
     */
    protected function mergeFromRequest($entity, array $attributesToMerge, Request $request): void
    {
        foreach ($attributesToMerge as $attributeToMerge => $attributeType) {
            if (!$request->request->has($attributeToMerge)) {
                continue;
            }
            $attributeData = $request->get($attributeToMerge);

            if ($this->isTypeADoctrineEntity($attributeType)) {
                $resourceId = $request->get($attributeToMerge)['id'];
                if ($resourceId !== null) {
                    $attributeData = $this->getResource($resourceId, $attributeType);
                }
            } else {
                $requestDataHandler = new RequestDataHandler($attributeData, $attributeType);
                $attributeData = $requestDataHandler->getValue();
            }

            $this->propertyAccessor->setValue($entity, $attributeToMerge, $attributeData);
        }
    }

    /**
     * Extracts data from request.
     *
     * @param Request $request
     * @param string  $key
     *
     * @return array
     */
    protected function extractData(Request $request, $key = null): array
    {
        $data = $request->request->all();

        return array_filter($data, function ($item) {
            return $item != 'undefined';
        });
    }

    protected function extractCriteria(Request $request, $default = [])
    {
        $query = $request->query->all();
        if (empty($query)) {
            return $default;
        }
        $criteria = array_merge(...array_map(function ($key, $value) {
            return [Inflector::camelize($key) => $value];
        }, array_keys($query), $query));

        return array_merge($criteria, $default);
    }

    protected function extractPiaId(Request $request, $key = null)
    {
        $data = $request->request->all();
        if ($key !== null) {
            $data = $data[$key];
        }

        return $data['pia_id'] ?? $request->get('pia_id');
    }

    protected function newFromArray($data, $piaId = null)
    {
        $entity = $this->serializer->fromArray($data, $this->getEntityClass());
        if ($piaId !== null) {
            $entity->setPia($this->getEntityManager()->getReference(Pia::class, $piaId));
        }

        return $entity;
    }

    protected function newFromRequest(Request $request, $piaId = null, $processingId = null)
    {
        $entity = $this->serializer->deserialize($request->getContent(), $this->getEntityClass(), 'json');
        if ($piaId !== null) {
            $entity->setPia($this->getEntityManager()->getReference(Pia::class, $piaId));
        }

        if ($processingId !== null) {
            $entity->setProcessing($this->getEntityManager()->getReference(Processing::class, $processingId));
        }

        return $entity;
    }

    /**
     * Get users assigned to this object.
     */
    public function getObjectUsersAssigned($object): array
    {
        $users = [];
        foreach (call_user_func([$object, 'getUsers']) as $user) {
            array_push($users, [
                'id' => $user->getId(),
                'firstName' => $user->getProfile()->getFirstName(),
                'lastName' => $user->getProfile()->getLastName(),
                'roles' => $user->getRoles()
            ]);
        }
        usort($users, [$this, 'sortRolesFirstname']);
        return $users;
    }

    public function sortRolesFirstname($a, $b): int
    {
        if ($a['roles'] == $b['roles'])
        {
            if ($a['firstName'] == $b['firstName'])
            {
                return 0;
            }

            return ($a['firstName'] < $b['firstName']) ? 1 : -1;
        }
        return ($a['roles'] < $b['roles']) ? -1 : 1;
    }

    /**
     * Check that current User can access resource.
     *
     * @throws AccessDeniedHttpException
     */
    public function canAccessRouteOr403(): void
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedHttpException();
        }
    }

    public function canAccessResourceOr403($resource): void
    {
        // Each controllers should define this method to perform a fine access control
    }

    public function showEntity(int $id): View
    {
        $entity = $this->getRepository()->find($id);

        if ($entity === null) {
            return $this->view($entity, Response::HTTP_NOT_FOUND);
        }

        $this->canAccessResourceOr403($entity);

        return $this->view($entity, Response::HTTP_OK);
    }

    /**
     * Send email to recipients with processing data.
     */
    public function assigningUsersEmail(EmailingService $emailing, $processing, $recipients): void
    {
        // notify users on processing
        $processingAttr = [$processing->getName(), '/processing/{id}',
            ['{id}' => $processing->getId()]];
        array_push($processingAttr, $processing);
        foreach ($recipients as $recipient) {
            $emailing->notifyAssignProcessingAndPiaUsers($processingAttr, $recipient,
                $this->getUser());
        }
    }

    /**
     * Check if $type is an object managed by Doctrine.
     *
     * @param string $type
     *
     * @return bool
     */
    private function isTypeADoctrineEntity(string $type): bool
    {
        if (self::$entityClasses === null) {
            // Kind of cached list
            self::$entityClasses = [];
            foreach ($this->getDoctrine()->getManager()->getMetadataFactory()->getAllMetadata() as $meta) {
                self::$entityClasses[] = $meta->getName();
            }
        }

        return in_array($type, self::$entityClasses);
    }

    abstract protected function getEntityClass();
}
