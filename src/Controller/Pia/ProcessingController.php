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
use PiaApi\DataHandler\RequestDataHandler;
use PiaApi\DataExchange\Descriptor\ProcessingDescriptor;
use PiaApi\DataExchange\Transformer\ProcessingTransformer;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\Folder;
use PiaApi\Entity\Pia\Processing;
use PiaApi\Entity\Pia\ProcessingComment;
use PiaApi\Entity\Pia\ProcessingDataType;
use PiaApi\Entity\Pia\ProcessingTemplate;
use PiaApi\Exception\ApiException;
use PiaApi\Exception\DataImportException;
use PiaApi\Services\EmailingService;
use PiaApi\Services\ProcessingService;
use PiaApi\Services\TrackingService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as Swg;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ProcessingController extends RestController
{
    /**
     * @var ProcessingService
     */
    protected $processingService;

    /**
     * @var ProcessingTransformer
     */
    protected $processingTransformer;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var EmailingService
     */
    protected $emailingService;

    /**
     * @var TrackingService
     */
    protected $trackingService;

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        ProcessingService $processingService,
        ProcessingTransformer $processingTransformer,
        SerializerInterface $serializer,
        EmailingService $emailingService,
        TrackingService $trackingService
    ) {
        parent::__construct($propertyAccessor, $serializer);
        $this->processingService = $processingService;
        $this->processingTransformer = $processingTransformer;
        $this->serializer = $serializer;
        $this->emailingService = $emailingService;
        $this->trackingService = $trackingService;
    }

    protected function getEntityClass()
    {
        return Processing::class;
    }

    /**
     * Lists all Processings reachable by the user.
     *
     * @Swg\Tag(name="Processing")
     *
     * @FOSRest\Get("/processings")
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
     *     description="Returns all Processings",
     *     @Swg\Schema(
     *         type="array",
     *         @Swg\Items(ref=@Nelmio\Model(type=Processing::class, groups={"Default"}))
     *     )
     * )
     *
     * @Security("is_granted('CAN_SHOW_PROCESSING')")
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        $structure = $this->getUser()->getStructure();

        $collection = $this->getRepository()
            ->getPaginatedProcessingsByStructure($structure);

        return $this->view($collection, Response::HTTP_OK);
    }

    /**
     * Shows one Processing by its ID.
     *
     * @Swg\Tag(name="Processing")
     *
     * @FOSRest\Get("/processings/{id}", requirements={"id"="\d+"})
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Processing"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns one Processing",
     *     @Swg\Schema(
     *         type="object",
     *         ref=@Nelmio\Model(type=Processing::class, groups={"Default"})
     *     )
     * )
     *
     * @Security("is_granted('CAN_SHOW_PROCESSING')")
     *
     * @return View
     */
    public function showAction(Request $request, $id)
    {
        return $this->showEntity($id);
    }

    /**
     * Creates a Processing.
     *
     * @Swg\Tag(name="Processing")
     *
     * @FOSRest\Post("/processings")
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="Processing",
     *     in="body",
     *     required=false,
     *     @Swg\Schema(
     *         type="object",
     *         required={"name", "redactors_id", "data_controller_id", "folder"},
     *         @Swg\Property(property="name", type="string"),
     *         @Swg\Property(property="redactors_id", type="number"),
     *         @Swg\Property(property="data_controller_id", type="number"),
     *         @Swg\Property(property="evaluator_pending_id", type="number"),
     *         @Swg\Property(property="data_protection_officer_pending_id", type="number"),
     *         @Swg\Property(property="folder", required={"id"}, type="object",
     *         @Swg\Property(property="id", type="number")),
     *         @Swg\Property(property="author", type="string"),
     *         @Swg\Property(property="designated_controller", type="string"),
     *         @Swg\Property(property="lawfulness", type="string"),
     *         @Swg\Property(property="minimization", type="string"),
     *         @Swg\Property(property="rights_guarantee", type="string"),
     *         @Swg\Property(property="exactness", type="string"),
     *         @Swg\Property(property="consent", type="string"),
     *         @Swg\Property(property="controllers", type="string"),
     *         @Swg\Property(property="non_eu_transfer", type="string"),
     *         @Swg\Property(property="context_of_implementation", type="string"),
     *         @Swg\Property(property="concerned_people", type="string"),
     *         @Swg\Property(property="processing_data_types", type="array", @Swg\Items(
     *              ref=@Nelmio\Model(type=ProcessingDataType::class, groups={"Default"})
     *         )),
     *         @Swg\Property(property="comments", type="array", @Swg\Items(
     *              ref=@Nelmio\Model(type=ProcessingComment::class, groups={"Default"})
     *         )),
     *         @Swg\Property(property="attachments", type="array", @Swg\Items(
     *              ref=@Nelmio\Model(type=ProcessingAttachment::class, groups={"Default"})
     *         )),
     *         @Swg\Property(property="consent_concerned_people", type="array"),
     *         @Swg\Property(property="access_concerned_people", type="array"),
     *         @Swg\Property(property="delete_concerned_people", type="array"),
     *         @Swg\Property(property="limit_concerned_people", type="array"),
     *         @Swg\Property(property="subcontractors_obligations", type="array"),
     *     ),
     *     description="The Processing content"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns the newly created Processing",
     *     @Swg\Schema(
     *         type="object",
     *         ref=@Nelmio\Model(type=Processing::class, groups={"Default"})
     *     )
     * )
     *
     * @Security("is_granted('CAN_CREATE_PROCESSING')")
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        $entity = $this->serializer->deserialize($request->getContent(), $this->getEntityClass(), 'json');
        $folder = $this->getResource($entity->getFolder()->getId(), Folder::class);
        list($redactors,
            $dataController,
            $evaluatorPending,
            $dataProtectionOfficerPending) = $this->getProcessingSupervisors($request);

        $this->canCreateResourceOr403($folder);

        $processing = $this->processingService->createProcessing(
            $request->get('name'),
            $folder,
            $redactors,
            $dataController,
            $evaluatorPending,
            $dataProtectionOfficerPending
        );

        // attach users' parent to that processing
        foreach ($processing->getFolder()->getUsers() as $user) {
            $processing->addUser($user);
        }

        // attach connected user (creator) to that processing
        $processing->addUser($this->getUser());
        $this->persist($processing);

        # 1/ assigning users email
        # do it after persist to get id!
        $recipients = $this->getAvailableRecipients($processing);
        $this->assigningUsersEmail($this->emailingService, $processing, $recipients);

        return $this->view($processing, Response::HTTP_OK);
    }

    /**
     * Updates a processing.
     *
     * @Swg\Tag(name="Processing")
     *
     * @FOSRest\Put("/processings/{id}", requirements={"id"="\d+"})
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Processing"
     * )
     * @Swg\Parameter(
     *     name="full Processing",
     *     in="body",
     *     required=false,
     *     @Swg\Schema(
     *         type="object",
     *         @Swg\Property(property="name", type="string"),
     *         @Swg\Property(property="redactors_id", type="array"),
     *         @Swg\Property(property="data_controller_id", type="number"),
     *         @Swg\Property(property="evaluator_pending_id", type="number"),
     *         @Swg\Property(property="data_protection_officer_pending_id", type="number"),
     *         @Swg\Property(property="author", type="string"),
     *         @Swg\Property(property="status", type="number"),
     *         @Swg\Property(property="description", type="string"),
     *         @Swg\Property(property="life_cycle", type="string"),
     *         @Swg\Property(property="storage", type="string"),
     *         @Swg\Property(property="standards", type="string"),
     *         @Swg\Property(property="processors", type="string"),
     *         @Swg\Property(property="designated_controller", type="string"),
     *         @Swg\Property(property="controllers", type="string"),
     *         @Swg\Property(property="lawfulness", type="string"),
     *         @Swg\Property(property="minimization", type="string"),
     *         @Swg\Property(property="rights_guarantee", type="string"),
     *         @Swg\Property(property="exactness", type="string"),
     *         @Swg\Property(property="consent", type="string"),
     *         @Swg\Property(property="concerned_people", type="string"),
     *         @Swg\Property(property="non_eu_transfer", type="string"),
     *         @Swg\Property(property="context_of_implementation", type="string"),
     *         @Swg\Property(property="processing_data_types", type="array", @Swg\Items(
     *              ref=@Nelmio\Model(type=ProcessingDataType::class, groups={"Default"})
     *         )),
     *         @Swg\Property(property="folder", required={"id"}, type="object",@Swg\Property(property="id", type="number")),
     *         @Swg\Property(property="recipients", type="string"),
     *         @Swg\Property(property="evaluationComment", type="string"),
     *         @Swg\Property(property="evaluationState", type="integer"),
     *         @Swg\Property(property="id", type="number")),
     *         @Swg\Property(property="comments", type="array", @Swg\Items(
     *              ref=@Nelmio\Model(type=ProcessingComment::class, groups={"Default"})
     *         )),
     *         @Swg\Property(property="attachments", type="array", @Swg\Items(
     *              ref=@Nelmio\Model(type=ProcessingAttachment::class, groups={"Default"})
     *         )),
     *         @Swg\Property(property="recipients", type="string"),
     *         @Swg\Property(property="consent_concerned_people", type="array"),
     *         @Swg\Property(property="access_concerned_people", type="array"),
     *         @Swg\Property(property="delete_concerned_people", type="array"),
     *         @Swg\Property(property="limit_concerned_people", type="array"),
     *         @Swg\Property(property="subcontractors_obligations", type="array"),
     *     ),
     *     description="The Processing content"
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
     * @Security("is_granted('CAN_EDIT_PROCESSING') or is_granted('CAN_MOVE_PROCESSING') or is_granted('CAN_EDIT_CARD_PROCESSING') or is_granted('CAN_EDIT_EVALUATION')")
     *
     * @return array
     */
    public function updateAction(Request $request, $id)
    {
        $processing = $this->getResource($id);
        $this->canAccessResourceOr403($processing);
        $this->canUpdateResourceOr403($processing);

        $updatableAttributes = [];
        $start_point = $processing->getFolder()->getId();

        if ( $this->isGranted('CAN_MOVE_PROCESSING') ) {
            $updatableAttributes['folder'] = Folder::class;
        }

        if ( $this->isGranted('CAN_EDIT_CARD_PROCESSING') ) {
            $updatableAttributes['name'] = RequestDataHandler::TYPE_STRING;
            $updatableAttributes['author'] = RequestDataHandler::TYPE_STRING;
            $updatableAttributes['designated_controller'] = RequestDataHandler::TYPE_STRING;
        }

        if ( $this->isGranted('CAN_EDIT_PROCESSING') ) {
            $updatableAttributes = array_merge($updatableAttributes, [
                'description'                => RequestDataHandler::TYPE_STRING,
                'processors'                 => RequestDataHandler::TYPE_STRING,
                'controllers'                => RequestDataHandler::TYPE_STRING,
                'non_eu_transfer'            => RequestDataHandler::TYPE_STRING,
                'recipients'                 => RequestDataHandler::TYPE_STRING,
                'life_cycle'                 => RequestDataHandler::TYPE_STRING,
                'storage'                    => RequestDataHandler::TYPE_STRING,
                'standards'                  => RequestDataHandler::TYPE_STRING,
                'context_of_implementation'  => RequestDataHandler::TYPE_STRING,
                'lawfulness'                 => RequestDataHandler::TYPE_STRING,
                'minimization'               => RequestDataHandler::TYPE_STRING,
                'rights_guarantee'           => RequestDataHandler::TYPE_STRING,
                'exactness'                  => RequestDataHandler::TYPE_STRING,
                'consent'                    => RequestDataHandler::TYPE_STRING,
                'concerned_people'           => RequestDataHandler::TYPE_STRING,
                'status'                     => RequestDataHandler::TYPE_INT,
                # @deprecated to be removed! 'informed_concerned_people'  => RequestDataHandler::TYPE_NULLABLE_ARRAY,
                'consent_concerned_people'   => RequestDataHandler::TYPE_NULLABLE_ARRAY,
                'access_concerned_people'    => RequestDataHandler::TYPE_NULLABLE_ARRAY,
                'delete_concerned_people'    => RequestDataHandler::TYPE_NULLABLE_ARRAY,
                'limit_concerned_people'     => RequestDataHandler::TYPE_NULLABLE_ARRAY,
                'subcontractors_obligations' => RequestDataHandler::TYPE_NULLABLE_ARRAY,
            ]);
        }

        if ( $this->isGranted('CAN_EDIT_EVALUATION') ) {
            $updatableAttributes = array_merge($updatableAttributes, [
                'status'                    => RequestDataHandler::TYPE_INT,
                'evaluation_comment'        => RequestDataHandler::TYPE_STRING,
                'evaluation_state'          => RequestDataHandler::TYPE_INT,
            ]);
        }

        # 1/ get users from request
        # do it before update!
        list($redactors,
            $dataController,
            $evaluatorPending,
            $dataProtectionOfficerPending) = $this->getProcessingSupervisors($request);
        # 2/ keep users who are in request and not in db
        $redactors = $this->intersectRedactorsFromRequestAndDb($processing, $redactors);

        // before merging!
        $this->notifyOrTrack($request, $processing);
        $this->mergeFromRequest($processing, $updatableAttributes, $request);
        $this->detachUsersAttachUsersNewPlace($processing, $start_point);
        $this->updateSupervisorsPia($request, $processing);
        $this->update($processing);

        # 3/ check those who are not null and keep them
        $recipients = $this->getRequestedRecipients(
            $redactors,
            $dataController,
            $evaluatorPending,
            $dataProtectionOfficerPending);
        # 4/ assigning users email
        $this->assigningUsersEmail($this->emailingService, $processing, $recipients);

        return $this->view($processing, Response::HTTP_OK);
    }

    /**
     * Deletes a processing.
     *
     * @Swg\Tag(name="Processing")
     *
     * @FOSRest\Delete("/processings/{id}", requirements={"id"="\d+"})
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Processing"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Empty content"
     * )
     *
     * @Security("is_granted('CAN_DELETE_PROCESSING')")
     *
     * @return array
     */
    public function deleteAction(Request $request, $id)
    {
        $processing = $this->getResource($id);
        $this->canAccessResourceOr403($processing);
        $this->canDeleteResourceOr403($processing);

        if (count($processing->getPias()) > 0) {
            throw new ApiException(Response::HTTP_CONFLICT, 'Processing must not contain Pias before being deleted', 701);
        }

        $this->remove($processing);

        return $this->view([], Response::HTTP_OK);
    }

    /**
     * Exports a PROCESSING.
     *
     * @Swg\Tag(name="Processing")
     *
     * @FOSRest\Get("/processings/{id}/export", requirements={"id"="\d+"})
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Processing"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns a PROCESSING",
     *     @Swg\Schema(
     *         type="object",
     *         ref=@Nelmio\Model(type=Processing::class, groups={"Default"})
     *     )
     * )
     *
     * @Security("is_granted('CAN_EXPORT_PIA')")
     *
     * @return array
     */
    public function exportAction(Request $request, $id)
    {
        $processing = $this->getResource($id);
        $this->canAccessResourceOr403($processing);
        $json = $this->processingTransformer->processingToJson($processing);
        return new Response($json, Response::HTTP_OK);
    }

    /**
     * Imports a PROCESSING.
     *
     * @Swg\Tag(name="Processing")
     *
     * @FOSRest\Post("/processings/import")
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns the imported PROCESSING"
     * )
     *
     * @Security("is_granted('CAN_IMPORT_PIA')")
     *
     * @return array
     */
    public function importAction(Request $request)
    {
        $data = $request->get('processing');
        $folderId = $request->get('folder_id');

        if ($folderId) {
            $folder = $this->getResource($folderId, Folder::class);
            $this->processingTransformer->setFolder($folder);
        }

        if (array_key_exists('supervisors', $data)) {
            $this->setSupervisors($data['supervisors']);            
        } else {
            throw new AccessDeniedHttpException('need supervisors object!');
        }

        try {
            $processing = $this->processingTransformer->jsonToProcessing($data);
            $this->persist($processing);
            $processing->setStatus(Processing::STATUS_DOING); # initialize status
            $descriptor = $this->processingTransformer->fromJson($data, ProcessingDescriptor::class);
            foreach ($descriptor->getPias() as $pia) {
                $processing->addPia($this->processingTransformer->extractPia($processing, $pia));
            }
            foreach ($descriptor->getProcessingDataTypes() as $types) {
                $processing->addProcessingDataType($this->processingTransformer->extractDataType($processing, $types));
            }
            $this->persist($processing);
        } catch (DataImportException $ex) {
            return $this->view(unserialize($ex->getMessage()), Response::HTTP_OK);
        }

        return $this->view($processing, Response::HTTP_OK);
    }

    /**
     * Creates a PIA from a template.
     *
     * @Swg\Tag(name="Processing")
     *
     * @FOSRest\Post("/processings/new-from-template/{id}")
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Processing template"
     * )
     * @Swg\Parameter(
     *     name="Processing",
     *     in="body",
     *     required=true,
     *     description="The Processing content",
     *     @Swg\Schema(
     *         type="object",
     *         ref=@Nelmio\Model(type=Processing::class, groups={"Default"})
     *     )
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns the newly created Processing",
     *     @Swg\Schema(
     *         type="object",
     *         ref=@Nelmio\Model(type=Processing::class, groups={"Default"})
     *     )
     * )
     *
     * @Security("is_granted('CAN_CREATE_PROCESSING')")
     *
     * @return array
     */
    public function createFromTemplateAction(Request $request, $id)
    {
        /** @var ProcessingTemplate $pTemplate */
        $pTemplate = $this->getDoctrine()->getRepository(ProcessingTemplate::class)->find($id);
        $folder = $this->getResource($request->get('folder', ['id' => -1])['id'], Folder::class);

        if ($pTemplate === null || $folder === null) {
            return $this->view($pTemplate, Response::HTTP_NOT_FOUND);
        }

        $this->processingTransformer->setFolder($folder);

        try {
            $tplData = json_decode($pTemplate->getData(), true);
            $processing = $this->processingTransformer->jsonToProcessing($tplData);
            $processing->setAuthor($request->get('author'));
            $processing->setDesignatedController($request->get('designated_controller'));

            $this->persist($processing);

            $descriptor = $this->processingTransformer->fromJson($tplData, ProcessingDescriptor::class);

            //only last PIA is used
            if (count($descriptor->getPias()) > 0) {
                $pias = $descriptor->getPias();
                $pia = $this->processingTransformer->extractPia($processing, end($pias));
                $processing->addPia($pia);
                $pia->setProcessing($processing);
                $pia->setStructure($folder->getStructure()); //@todo to be removed, a Pia do not need a structure
                $this->persist($pia);
            }

            foreach ($descriptor->getProcessingDataTypes() as $types) {
                $processing->addProcessingDataType($this->processingTransformer->extractDataType($processing, $types));
            }

            $processing->setTemplate($pTemplate);

            $this->persist($processing);
        } catch (DataImportException $ex) {
            return $this->view(unserialize($ex->getMessage()), Response::HTTP_BAD_REQUEST);
        }

        return $this->view($processing, Response::HTTP_OK);
    }

    /**
     * Checks permissions while creating processing.
     * the error code sent is managed by front for translation.
     */
    public function canCreateResourceOr403($folder): void
    {
        // prevent creating processing by the root
        if ($folder->isRoot()) {
            // can not create processing by the root.
            throw new AccessDeniedHttpException('messages.http.403.1');
        }

        // prevent creating processing if no access to folder
        # FIXME is this relevant? && $folder->hasUsers()
        if (!$folder->canAccess($this->getUser()) && !$this->isGranted('ROLE_DPO')) {
            // can not create processing if no access to its folder.
            throw new AccessDeniedHttpException('messages.http.403.4');
        }
    }

    /**
     * Checks permissions while updating processing.
     * the error code sent is managed by front for translation.
     */
    public function canUpdateResourceOr403($resource): void
    {
        // prevent updating folder if no access to folder
        if (!$resource->canShow($this->getUser()) && !$this->isGranted('ROLE_DPO')) {
            // you are not allowed to update this processing.
            throw new AccessDeniedHttpException('messages.http.403.3');
        }
    }

    /**
     * If processing moved: detach users and attach users from parent.
     */
    public function updateSupervisorsPia($request, $processing): void
    {
        $content = json_decode($request->getContent(), true);
        foreach ([
            ['redactors_id', ['addRedactor', 'removeAllRedactors']],
            ] as $supervisor) {
            $this->methodArraySupervisors($processing, $content, $supervisor);
        }
        foreach ([
            ['data_controller_id', 'setDataController'],
            ['evaluator_pending_id', 'setEvaluatorPending'],
            ['data_protection_officer_pending_id', 'setDataProtectionOfficerPending'],
            ] as $supervisor) {
            $this->methodSupervisors($processing, $content, $supervisor);
        }
    }

    /**
     * If processing moved: detach users and attach users from parent.
     */
    public function detachUsersAttachUsersNewPlace($processing, $start_point): void
    {
        if ($start_point != $processing->getFolder()->getId()) {
            // detach processing's users
            foreach ($processing->getUsers() as $user) {
                $processing->removeUser($user);
            }
            // add user that moves processing
            $processing->addUser($this->getUser());
            // attach users' parent to that processing
            foreach ($processing->getFolder()->getUsers() as $user) {
                $processing->addUser($user);
            }
        }
    }

    /**
     * Checks permissions while deleting processing.
     * the error code sent is managed by front for translation.
     */
    public function canDeleteResourceOr403($resource): void
    {
        // prevent deleting folder if no access to folder
        if (!$resource->getFolder()->canAccess($this->getUser()) && !$this->isGranted('ROLE_DPO')) {
            // you are not allowed to delete this processing.
            throw new AccessDeniedHttpException('messages.http.403.7');
        }
    }

    /**
     * Gets redactors, dataController, evaluator and dataProtectionOfficer,
     * from request if exist.
     */
    private function getProcessingSupervisors($request): array
    {
        // redactors
        $redactors = [];
        $redactorIds = $request->get('redactors_id');
        if (null != $redactorIds) {
            foreach ($redactorIds as $key) {
                array_push($redactors, $this->getResource($key, User::class));
            }
        }

        // data controller
        $dataControllerId = $request->get('data_controller_id');
        $dataController = (null != $dataControllerId)
            ? $this->getResource($dataControllerId, User::class)
            : null;

        // evaluator data for pia creation
        $evaluatorPendingId = $request->get('evaluator_pending_id');
        $evaluatorPending = (null != $evaluatorPendingId)
            ? $this->getResource($evaluatorPendingId, User::class)
            : null;

        // dpo data for pia creation
        $dataProtectionOfficerPendingId = $request->get('data_protection_officer_pending_id');
        $dataProtectionOfficerPending = (null != $dataProtectionOfficerPendingId)
            ? $this->getResource($dataProtectionOfficerPendingId, User::class)
            : null;

        return [$redactors, $dataController, $evaluatorPending, $dataProtectionOfficerPending];
    }

    /**
     * Gets redactors, dataController, evaluator and dataProtectionOfficer.
     */
    private function getAvailableRecipients($processing): array
    {
        $recipients = [];
        foreach ($processing->getRedactors() as $redactor) {
            array_push($recipients, $redactor);
        }
        array_push($recipients, $processing->getDataController());
        if (null !== $processing->getEvaluatorPending()) {
            array_push($recipients, $processing->getEvaluatorPending());
        }
        if (null !== $processing->getDataProtectionOfficerPending()) {
            array_push($recipients, $processing->getDataProtectionOfficerPending());
        }
        return $recipients;
    }

    /**
     * Gets redactors, dataController, evaluator and dataProtectionOfficer,
     * if they are different from those who are in db.
     */
    private function getRequestedRecipients(
            $redactors,
            $dataController,
            $evaluatorPending,
            $dataProtectionOfficerPending): array
    {
        $recipients = [];
        if (!empty($redactors)) {
            foreach ($redactors as $redactor) {
                array_push($recipients, $redactor);
            }
        }
        if (null !== $dataController) {
            array_push($recipients, $dataController);
        }
        if (null !== $evaluatorPending) {
            array_push($recipients, $evaluatorPending);
        }
        if (null !== $dataProtectionOfficerPending) {
            array_push($recipients, $dataProtectionOfficerPending);
        }
        return $recipients;
    }

    /**
     * Keeps users who are in request and not in db.
     */
    private function intersectRedactorsFromRequestAndDb($processing, $redactors): array
    {
        $arrDb = [];
        foreach ($processing->getRedactors() as $redactor) {
            $arrDb[] = $redactor->getId();
        }

        $arrRequest = [];
        if (!empty($redactors)) {
            foreach ($redactors as $redactor) {
                if (!in_array($redactor->getId(), $arrDb)) {
                    array_push($arrRequest, $redactor);
                }
            }
        }
        return $arrRequest;
    }

    private function setSupervisors($supervisors)
    {

        if (array_key_exists('redactors_id', $supervisors))
        {
            foreach ($supervisors['redactors_id'] as $key) {
                $this->processingTransformer->addRedactor($this->getResource($key, User::class));
            }
        }
        if (array_key_exists('data_controller_id', $supervisors))
        {
            $dataController = $this->getResource($supervisors['data_controller_id'], User::class);
            $this->processingTransformer->setDataController($dataController);            
        }
        if (array_key_exists('evaluator_pending_id', $supervisors))
        {
            // evaluator data for pia creation
            $evaluatorPendingId = $supervisors['evaluator_pending_id'];
            $evaluatorPending = (null != $evaluatorPendingId)
                ? $this->getResource($evaluatorPendingId, User::class)
                : null;
            $this->processingTransformer->setEvaluatorPending($evaluatorPending);
        }
        if (array_key_exists('data_protection_officer_pending_id', $supervisors))
        {
            // dpo data for pia creation
            $dataProtectionOfficerPendingId = $supervisors['data_protection_officer_pending_id'];
            $dataProtectionOfficerPending = (null != $dataProtectionOfficerPendingId)
                ? $this->getResource($dataProtectionOfficerPendingId, User::class)
                : null;
            $this->processingTransformer->setDataProtectionOfficerPending($dataProtectionOfficerPending);
        }
    }

    private function methodSupervisors($processing, $content, $supervisor): void
    {
        // property_id
        if (array_key_exists($supervisor[0], $content)) {
            $id = $content[$supervisor[0]]; # null or string
            if (null != $id && '' != $id) {
                $this->callUserMethod($processing, $id, $supervisor[1]);
            }
        }
    }

    private function methodArraySupervisors($processing, $content, $supervisor): void
    {
        // property_id
        if (array_key_exists($supervisor[0], $content)) {
            $ids = $content[$supervisor[0]]; # array
            if (0 < count($ids)) {
                // remove all to be restored!
                call_user_func([$processing, $supervisor[1][1]]);
                foreach ($ids as $id) {
                    $this->callUserMethod($processing, $id, $supervisor[1][0]);
                }
            }
        }
    }

    private function callUserMethod($processing, $userid, $method): void
    {
        $user = $this->getResource($userid, User::class);
        if (null !== $user) {
            // method_name, change pia as well
            call_user_func([$processing, $method], $user);
        }        
    }

    /**
     * Some notifications to send.
     * specifications: #1, #5
     */
    private function notifyOrTrack($request, $processing): void
    {
        if ($processing->canAskForProcessingEvaluation($request))
        {
            // notify evaluator on last page of processing
            $processingAttr = [$processing->getName(), '/processing/{id}', ['{id}' => $processing->getId()]];
            array_push($processingAttr, $processing);
            $recipient = $processing->getEvaluatorPending();
            if (null == $recipient)
            {
                // evaluator not defined!
                throw new AccessDeniedHttpException('evaluator not defined!');
            } else {
                $sources = $processing->getRedactors();
                $this->emailingService->notifyAskForProcessingEvaluation($processingAttr, $recipient, $sources);
            }
            # FIXME add an evaluation request tracking? unfortunately, at this point we do not know the pia!
        }

        if ($processing->canEmitEvaluatorEvaluation($request))
        {
            // notify redactor
            $processingAttr = [$processing->getName(), '/processing/{id}', ['{id}' => $processing->getId()]];
            array_push($processingAttr, $processing);
            $source = $processing->getEvaluatorPending();
            foreach ($processing->getRedactors() as $recipient) {
                $this->emailingService->notifyEmitEvaluatorEvaluation($processingAttr, $recipient, $source);
            }
        }
    }
}
