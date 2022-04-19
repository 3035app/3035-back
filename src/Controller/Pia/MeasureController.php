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
use PiaApi\Entity\Pia\Measure;
use PiaApi\Entity\Pia\Pia;
use PiaApi\Services\TrackingService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as Swg;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class MeasureController extends PiaSubController
{
    /**
     * @var trackingService
     */
    protected $trackingService;

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        SerializerInterface $serializer,
        TrackingService $trackingService
    ) {
        parent::__construct($propertyAccessor, $serializer);
        $this->trackingService = $trackingService;
    }

    /**
     * Lists all Answers for a specific Treatment.
     *
     * @Swg\Tag(name="Measure")
     *
     * @FOSRest\Get("/pias/{piaId}/measures")
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="piaId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the PIA"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns all Answers for given Treatment",
     *     @Swg\Schema(
     *         type="array",
     *         @Swg\Items(ref=@Nelmio\Model(type=Measure::class, groups={"Default"}))
     *     )
     * )
     *
     * @Security("is_granted('CAN_SHOW_MEASURE')")
     */
    public function listAction(Request $request, $piaId)
    {
        return parent::listAction($request, $piaId);
    }

    /**
     * Shows one Measure by its ID and specific Treatment.
     *
     * @Swg\Tag(name="Measure")
     *
     * @FOSRest\Get("/pias/{piaId}/measures/{id}")
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="piaId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the PIA"
     * )
     * @Swg\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Measure"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns one Measure",
     *     @Swg\Schema(
     *         type="object",
     *         ref=@Nelmio\Model(type=Measure::class, groups={"Default"})
     *     )
     * )
     *
     * @Security("is_granted('CAN_SHOW_MEASURE')")
     */
    public function showAction(Request $request, $piaId, $id)
    {
        return parent::showAction($request, $piaId, $id);
    }

    /**
     * Creates a Measure for a specific Treatment.
     *
     * @Swg\Tag(name="Measure")
     *
     * @FOSRest\Post("/pias/{piaId}/measures")
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="piaId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the PIA"
     * )
     * @Swg\Parameter(
     *     name="Measure",
     *     in="body",
     *     required=true,
     *     @Swg\Schema(
     *         type="object",
     *         required={"title", "content","placeholder"},
     *         @Swg\Property(property="title", type="string"),
     *         @Swg\Property(property="content", type="string"),
     *         @Swg\Property(property="placeholder", type="string")
     *     ),
     *     description="The Measure content"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns the newly created Measure",
     *     @Swg\Schema(
     *         type="object",
     *         ref=@Nelmio\Model(type=Measure::class, groups={"Default"})
     *     )
     * )
     *
     * @Security("is_granted('CAN_CREATE_MEASURE')")
     */
    public function createAction(Request $request, $piaId)
    {
        $view = parent::createAction($request, $piaId);
        $this->logActivityLastUpdate($piaId);
        return $view;
    }

    /**
     * Updates a Measure for a specific Treatment.
     *
     * @Swg\Tag(name="Measure")
     *
     * @FOSRest\Put("/pias/{piaId}/measures/{id}")
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="piaId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the PIA"
     * )
     * @Swg\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Measure"
     * )
     * @Swg\Parameter(
     *     name="Measure",
     *     in="body",
     *     required=true,
     *     @Swg\Schema(
     *         type="object",
     *         @Swg\Property(property="title", type="string"),
     *         @Swg\Property(property="content", type="string"),
     *         @Swg\Property(property="placeholder", type="string")
     *     ),
     *     description="The Measure content"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns the updated Measure",
     *     @Swg\Schema(
     *         type="object",
     *         ref=@Nelmio\Model(type=Measure::class, groups={"Default"})
     *     )
     * )
     *
     * @Security("is_granted('CAN_EDIT_MEASURE')")
     */
    public function updateAction(Request $request, $piaId, $id)
    {
        $view = parent::updateAction($request, $piaId, $id);
        $this->logActivityLastUpdate($piaId);
        return $view;
    }

    /**
     * Deletes a Measure for a specific Treatment.
     *
     * @Swg\Tag(name="Measure")
     *
     * @FOSRest\Delete("pias/{piaId}/measures/{id}")
     *
     * @Swg\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="The API token. e.g.: Bearer <TOKEN>"
     * )
     * @Swg\Parameter(
     *     name="piaId",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the PIA"
     * )
     * @Swg\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="The ID of the Measure"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Empty content"
     * )
     *
     * @Security("is_granted('CAN_DELETE_MEASURE')")
     *
     * @return array
     */
    public function deleteAction(Request $request, $piaId, $id)
    {
        return parent::deleteAction($request, $piaId, $id);
    }

    public function logActivityLastUpdate($piaId)
    {
        $pia = $this->getResource($piaId, Pia::class);
        if (null !== $pia) {
            $this->trackingService->logActivityLastUpdate($pia->getProcessing());
        }
    }

    protected function getEntityClass()
    {
        return Measure::class;
    }
}
