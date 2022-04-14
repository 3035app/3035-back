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
use PiaApi\Entity\Pia\Evaluation;
use PiaApi\Entity\Pia\Pia;
use PiaApi\Services\EmailingService;
use PiaApi\Services\TrackingService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as Swg;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EvaluationController extends PiaSubController
{
    /**
     * @var EmailingService
     */
    protected $emailingService;

    /**
     * @var trackingService
     */
    protected $trackingService;

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        SerializerInterface $serializer,
        EmailingService $emailingService,
        TrackingService $trackingService
    ) {
        parent::__construct($propertyAccessor, $serializer);
        $this->emailingService = $emailingService;
        $this->trackingService = $trackingService;
    }

    /**
     * Lists all Answers for a specific Treatment.
     *
     * @Swg\Tag(name="Evaluation")
     *
     * @FOSRest\Get("/pias/{piaId}/evaluations")
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
     *     description="Returns all Answers of given Treatment",
     *     @Swg\Schema(
     *         type="array",
     *         @Swg\Items(ref=@Nelmio\Model(type=Evaluation::class, groups={"Default"}))
     *     )
     * )
     *
     * @Security("is_granted('CAN_SHOW_EVALUATION')")
     */
    public function listAction(Request $request, $piaId)
    {
        return parent::listAction($request, $piaId);
    }

    /**
     * Shows one Evaluation by its ID and specific Treatment.
     *
     * @Swg\Tag(name="Evaluation")
     *
     * @FOSRest\Get("/pias/{piaId}/evaluations/{id}")
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
     *     description="The ID of the Evaluation"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns one Evaluation",
     *     @Swg\Schema(
     *         type="object",
     *         ref=@Nelmio\Model(type=Evaluation::class, groups={"Default"})
     *     )
     * )
     *
     * @Security("is_granted('CAN_SHOW_EVALUATION')")
     */
    public function showAction(Request $request, $piaId, $id)
    {
        return parent::showAction($request, $piaId, $id);
    }

    /**
     * Creates an Evaluation for a specific Treatment.
     *
     * @Swg\Tag(name="Evaluation")
     *
     * @FOSRest\Post("/pias/{piaId}/evaluations")
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
     *     name="Evaluation",
     *     in="body",
     *     required=true,
     *     @Swg\Schema(
     *         type="object",
     *         required={"action_plan_comment", "evaluation_comment","person_in_charge","reference_to"},
     *         @Swg\Property(property="action_plan_comment", type="string"),
     *         @Swg\Property(property="evaluation_comment", type="string"),
     *         @Swg\Property(property="global_status", type="number"),
     *         @Swg\Property(property="person_in_charge", type="string"),
     *         @Swg\Property(property="reference_to", type="string"),
     *         @Swg\Property(property="status", type="number")
     *     ),
     *     description="The Evaluation content"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns the newly created Evaluation",
     *     @Swg\Schema(
     *         type="object",
     *         ref=@Nelmio\Model(type=Evaluation::class, groups={"Default"})
     *     )
     * )
     *
     * @Security("is_granted('CAN_CREATE_EVALUATION')")
     */
    public function createAction(Request $request, $piaId)
    {
        $view = parent::createAction($request, $piaId);
        $evaluation = $this->getEvaluation($piaId, $request);
        if (null !== $evaluation) {
            $this->notifyEvaluator($request, $evaluation);
        }
        return $view;
    }

    /**
     * Updates an Evaluation for a specific Treatment.
     *
     * @Swg\Tag(name="Evaluation")
     *
     * @FOSRest\Put("/pias/{piaId}/evaluations/{id}")
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
     *     description="The ID of the Evaluation"
     * )
     * @Swg\Parameter(
     *     name="Evaluation",
     *     in="body",
     *     required=true,
     *     @Swg\Schema(
     *         type="object",
     *         @Swg\Property(property="action_plan_comment", type="string"),
     *         @Swg\Property(property="evaluation_comment", type="string"),
     *         @Swg\Property(property="global_status", type="number"),
     *         @Swg\Property(property="person_in_charge", type="string"),
     *         @Swg\Property(property="reference_to", type="string"),
     *         @Swg\Property(property="status", type="number")
     *     ),
     *     description="The Evaluation content"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Returns the updated Evaluation",
     *     @Swg\Schema(
     *         type="object",
     *         ref=@Nelmio\Model(type=Evaluation::class, groups={"Default"})
     *     )
     * )
     *
     * @Security("is_granted('CAN_EDIT_EVALUATION')")
     */
    public function updateAction(Request $request, $piaId, $id)
    {
        $view = parent::updateAction($request, $piaId, $id);
        $evaluation = $this->getResource($id, Evaluation::class);
        if (null !== $evaluation) {
            $this->notifyRedactor($request, $evaluation);
            $this->notifyDpo($request, $evaluation->getPia());
        }
        return $view;
    }

    /**
     * Deletes an Evaluation for a specific Treatment.
     *
     * @Swg\Tag(name="Evaluation")
     *
     * @FOSRest\Delete("pias/{piaId}/evaluations/{id}")
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
     *     description="The ID of the Evaluation"
     * )
     *
     * @Swg\Response(
     *     response=200,
     *     description="Empty content"
     * )
     *
     * @Security("is_granted('CAN_DELETE_EVALUATION')")
     *
     * @return array
     */
    public function deleteAction(Request $request, $piaId, $id)
    {
        return parent::deleteAction($request, $piaId, $id);
    }

    protected function getEntityClass()
    {
        return Evaluation::class;
    }

    private function getEvaluation($piaId, $request): ?Evaluation
    {
        $pia = $this->getResource($piaId, Pia::class);
        $referenceTo = $request->get('reference_to');
        foreach ($pia->getEvaluations() as $item)
        {
            if ($referenceTo == $item->getReferenceTo())
            {
                return $item;
            }
        }
        return null;
    }

    /**
     * Some notifications to send.
     * specifications: #1
     */
    private function notifyEvaluator($request, $evaluation): void
    {
        $pia = $evaluation->getPia();
        $processing = $pia->getProcessing();

        // notify evaluator on each page of pia
        $piaAttr = $this->getEvaluationRoute($evaluation);
        array_push($piaAttr, $pia);
        $recipient = $pia->getEvaluator();
        $source = $processing->getRedactor();
        $this->emailingService->notifyAskForPiaEvaluation($piaAttr, $recipient, $source);

        // check if all evaluations requested for that pia
        // and one evaluation requested for that processing
        if ($pia->canLogEvaluationRequest())
        {
            # add an evaluation request tracking
            $this->trackingService->logActivityEvaluationRequest($processing);
        }
    }

    /**
     * Some notifications to send.
     * specifications: #6
     */
    private function notifyRedactor($request, $evaluation): void
    {
        //check if status and global status match this state
        if ($evaluation->canEmitPiaEvaluatorEvaluation($request))
        {
            $pia = $evaluation->getPia();
            $processing = $pia->getProcessing();

            // notify redactor after evaluating each page of pia
            $piaAttr = $this->getEvaluationRoute($evaluation);
            array_push($piaAttr, $pia);
            $recipient = $processing->getRedactor();
            $source = $pia->getEvaluator();
            $this->emailingService->notifyEmitPiaEvaluatorEvaluation($piaAttr, $recipient, $source);

/*throw new AccessDeniedHttpException('logActivityEvaluation');
            # add an evaluation tracking
            $this->trackingService->logActivityEvaluation($processing);*/
        }
    }

    /**
     * Some notifications to send.
     * specifications: #8
     */
    private function notifyDpo($request, $pia): void
    {
        // check if all evaluations are acceptable, then notify dpo!
        // at this point, all evaluations are created
        if ($pia->isPiaEvaluationsAcceptable())
        {
            // notify dpo
            $piaAttr = [$pia->__toString(), '/entry/{pia_id}/section/4/item/3', ['{pia_id}' => $pia->getId()]];
            array_push($piaAttr, $pia);
            $recipient = $pia->getDataProtectionOfficer();
            $source = $pia->getEvaluator();
            $this->emailingService->notifySubmitPiaToDpo($piaAttr, $recipient, $source);
        }
    }

    private function getEvaluationRoute($evaluation): array
    {
        $params = [
            '{pia_id}' => $evaluation->getPia()->getId(),
            '{section_id}' => $evaluation->getSection(),
            '{item_id}' => $evaluation->getItemReference(),
        ];
        return [$evaluation, '/entry/{pia_id}/section/{section_id}/item/{item_id}', $params];
    }
}
