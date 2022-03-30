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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as Swg;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class EvaluationController extends PiaSubController
{
    /**
     * @var EmailingService
     */
    protected $emailingService;

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        SerializerInterface $serializer,
        EmailingService $emailingService
    ) {
        parent::__construct($propertyAccessor, $serializer);
        $this->emailingService = $emailingService;
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
     */
    private function notifyEvaluator($request, $evaluation): void
    {
        // notify evaluator on each page of pia
        $piaAttr = [$evaluation, $request->get('_route'), ['piaId' => $evaluation->getPia()->getId()]];
        $userEmail = $evaluation->getPia()->getEvaluator()->getEmail();
        $userName = $evaluation->getPia()->getEvaluator()->getProfile()->getFullname();
        $this->emailingService->notifyAskForPiaEvaluation($piaAttr, $userEmail, $userName);
    }

    /**
     * Some notifications to send.
     */
    private function notifyRedactor($request, $evaluation): void
    {
        //check if status and global status match this state
        if ($this->canEmitPiaEvaluatorEvaluation($request, $evaluation))
        {
            // notify redactor after evaluating each page of pia
            $piaAttr = [$evaluation, $request->get('_route'), ['piaId' => $evaluation->getPia()->getId()]];
            $userEmail = $evaluation->getPia()->getProcessing()->getRedactor()->getEmail();
            $userName = $evaluation->getPia()->getProcessing()->getRedactor()->getProfile()->getFullname();
            $this->emailingService->notifyEmitPiaEvaluatorEvaluation($piaAttr, $userEmail, $userName);
        }
    }
}
