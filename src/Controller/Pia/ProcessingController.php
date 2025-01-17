<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Controller\Pia;

use PiaApi\Services\ProcessingService;
use PiaApi\Entity\Pia\Processing;
use PiaApi\Entity\Pia\ProcessingTemplate;
use PiaApi\Entity\Pia\Folder;
use PiaApi\DataHandler\RequestDataHandler;
use PiaApi\Entity\Pia\ProcessingDataType;
use PiaApi\Entity\Pia\ProcessingComment;
use PiaApi\Exception\ApiException;
use JMS\Serializer\SerializerInterface;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation as Nelmio;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as Swg;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use PiaApi\DataExchange\Transformer\ProcessingTransformer;
use PiaApi\Exception\DataImportException;
use PiaApi\DataExchange\Descriptor\ProcessingDescriptor;

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

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        ProcessingService $processingService,
        ProcessingTransformer $processingTransformer,
        SerializerInterface $serializer
    ) {
        parent::__construct($propertyAccessor, $serializer);

        $this->processingService = $processingService;
        $this->processingTransformer = $processingTransformer;
        $this->serializer = $serializer;
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
     *         required={"name", "author", "designated_controller", "folder"},
     *         @Swg\Property(property="name", type="string"),
     *         @Swg\Property(property="author", type="string"),
     *         @Swg\Property(property="designated_controller", type="string"),
     *         @Swg\Property(property="folder", required={"id"}, type="object",
     *         @Swg\Property(property="id", type="number")),
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

        $processing = $this->processingService->createProcessing(
            $request->get('name'),
            $folder,
            $request->get('author'),
            $request->get('designated_controller')
        );

        $this->persist($processing);

        return $this->view($processing, Response::HTTP_OK);
    }

    /**
     * Updates a processing.
     *
     * @Swg\Tag(name="Processing")
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
     *         @Swg\Property(property="recipients", type="string")
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
     * @FOSRest\Put("/processings/{id}", requirements={"id"="\d+"})
     *
     * @Security("is_granted('CAN_EDIT_PROCESSING') or is_granted('CAN_MOVE_PROCESSING') or is_granted('CAN_EDIT_CARD_PROCESSING') or is_granted('CAN_EDIT_EVALUATION')")
     *
     * @return array
     */
    public function updateAction(Request $request, $id)
    {
        $processing = $this->getResource($id);
        $this->canAccessResourceOr403($processing);
        
        $updatableAttributes = [];
        
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
                'description'               => RequestDataHandler::TYPE_STRING,
                'processors'                => RequestDataHandler::TYPE_STRING,
                'controllers'               => RequestDataHandler::TYPE_STRING,
                'non_eu_transfer'           => RequestDataHandler::TYPE_STRING,
                'recipients'                => RequestDataHandler::TYPE_STRING,
                'life_cycle'                => RequestDataHandler::TYPE_STRING,
                'storage'                   => RequestDataHandler::TYPE_STRING,
                'standards'                 => RequestDataHandler::TYPE_STRING,
                'context_of_implementation' => RequestDataHandler::TYPE_STRING,
                'lawfulness'                => RequestDataHandler::TYPE_STRING,
                'minimization'              => RequestDataHandler::TYPE_STRING,
                'rights_guarantee'          => RequestDataHandler::TYPE_STRING,
                'exactness'                 => RequestDataHandler::TYPE_STRING,
                'consent'                   => RequestDataHandler::TYPE_STRING,
                'concerned_people'          => RequestDataHandler::TYPE_STRING,
                'status'                    => RequestDataHandler::TYPE_INT,
            ]);
        }

        if ( $this->isGranted('CAN_EDIT_EVALUATION') ) {
            $updatableAttributes = array_merge($updatableAttributes, [
                'status'                    => RequestDataHandler::TYPE_INT,
                'evaluation_comment'        => RequestDataHandler::TYPE_STRING,
                'evaluation_state'          => RequestDataHandler::TYPE_INT,
            ]);
        }
        
        $this->mergeFromRequest($processing, $updatableAttributes, $request);
        
        $this->update($processing);

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

        try {
            $processing = $this->processingTransformer->jsonToProcessing($data);
            $this->persist($processing);

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
}
