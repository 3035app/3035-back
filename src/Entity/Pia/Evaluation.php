<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Entity\Pia;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;
use PiaApi\Entity\Pia\Traits\EvaluationStateTrait;
use PiaApi\Entity\Pia\Traits\HasPiaTrait;
use PiaApi\Entity\Pia\Traits\ResourceTrait;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @ORM\Entity
 * @ORM\Table(name="pia_evaluation")
 */
class Evaluation implements Timestampable
{
    use EvaluationStateTrait, HasPiaTrait, ResourceTrait, TimestampableEntity;

    const EVALUATION_STATE_NONE = 0;
    const EVALUATION_STATE_TO_CORRECT = 1;
    const EVALUATION_STATE_IMPROVABLE = 2;
    const EVALUATION_STATE_ACCEPTABLE = 3;

    /**
     * @ORM\ManyToOne(targetEntity="Pia", inversedBy="evaluations")
     * @JMS\Exclude()
     *
     * @var Pia
     */
    protected $pia;

    /**
     * @ORM\Column(type="smallint")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var int
     */
    protected $status = 0;

    /**
     * @ORM\Column(type="string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $referenceTo;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $actionPlanComment;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $evaluationComment;

    /**
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var DateTime
     */
    protected $evaluationDate;

    /**
     * @ORM\Column(type="json")
     * @JMS\Type("array")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array
     */
    protected $gauges;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var DateTime
     */
    protected $estimatedImplementationDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $personInCharge;

    /**
     * @ORM\Column(type="smallint")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var int
     */
    protected $globalStatus = 0;

    /**
     * @return string
     */
    public function getSection(): string
    {
        $points = explode('.', $this->getReferenceTo());
        if (0 < count($points))
        {
            return $points[0];
        }
        return null;
    }

    /**
     * @return string
     */
    public function getItemReference(): string
    {
        $points = explode('.', $this->getReferenceTo());
        if (1 < count($points))
        {
            return $points[1];
        }
        return null;
    }

    /**
     * @return string
     */
    public function getReferenceTo(): string
    {
        return $this->referenceTo;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getGlobalStatus(): int
    {
        return $this->globalStatus;
    }

    /**
     * @return int
     */
    public function getEvaluationState(): int
    {
        return $this->getStatus();
    }

    /**
     * @return bool
     */
    public function canEmitPiaEvaluatorEvaluation($request): bool
    {
        # add an evaluation
        return $request->get('status') != $this->getStatus();
    }

    /**
     * @return bool
     */
    public function canEmitPiaEvaluatorCancelEvaluation($request): bool
    {
        $old_status = $request->get('global_status');
        # cancel evaluation
        return 2 > $old_status && $old_status != $this->getGlobalStatus();
    }

    /**
     * @return string
     **/
    public function __toString()
    {
        return sprintf('%s (section %s)', $this->getPia()->getProcessing()->getName(), $this->getItemReference());
    }
}
