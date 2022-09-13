<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\DataExchange\Transformer;

use PiaApi\Entity\Pia\Evaluation;
use PiaApi\Entity\Pia\Pia;
use PiaApi\DataExchange\Descriptor\EvaluationDescriptor;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EvaluationTransformer extends AbstractTransformer
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var Pia|null
     */
    protected $pia = null;

    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function setPia(Pia $pia)
    {
        $this->pia = $pia;
    }

    public function getPia(): Pia
    {
        return $this->pia;
    }

    public function toEvaluation(EvaluationDescriptor $descriptor): Evaluation
    {
        $evaluation = new Evaluation();
        $evaluation->setPia($descriptor->getPia());
        $evaluation->setStatus($descriptor->getStatus());
        $evaluation->setReferenceTo($descriptor->getReferenceTo());
        $evaluation->setEvaluationComment($descriptor->getEvaluationComment());
        $evaluation->setEvaluationDate($descriptor->getEvaluationDate());
        $evaluation->setGlobalStatus($descriptor->getGlobalStatus());
        return $evaluation;
    }

    public function fromEvaluation(Evaluation $evaluation): EvaluationDescriptor
    {
        $descriptor = new EvaluationDescriptor(
            $evaluation->getPia(),
            $evaluation->getStatus(),
            $evaluation->getReferenceTo(),
            $evaluation->getEvaluationComment(),
            $evaluation->getEvaluationDate(),
            $evaluation->getGlobalStatus(),
        );
        return $descriptor;
    }

    public function importEvaluations(array $evaluations): array
    {
        $descriptors = [];
        foreach ($evaluations as $evaluation) {
            $descriptors[] = $this->fromEvaluation($evaluation);
        }
        return $descriptors;
    }

    public function evaluationToJson(Evaluation $evaluation): string
    {
        $descriptor = $this->fromEvaluation($evaluation);
        return $this->toJson($descriptor);
    }

    public function jsonToEvaluation(array $json): Evaluation
    {
        $descriptor = $this->fromJson($json, EvaluationDescriptor::class);
        return $this->toEvaluation($descriptor);
    }
}
