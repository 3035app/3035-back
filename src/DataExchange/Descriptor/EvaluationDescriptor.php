<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\DataExchange\Descriptor;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use PiaApi\Entity\Pia\Pia;

class EvaluationDescriptor extends AbstractDescriptor
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     * @var string
     */
    protected $pia;

    /**
     * @JMS\Type("int")
     * @JMS\Groups({"Default", "Export"})
     * @var int
     */
    protected $status = 0;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     * @var string
     */
    protected $referenceTo;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     * @var string|null
     */
    protected $evaluationComment;

    /**
     * @JMS\Type("DateTime")
     * @JMS\Groups({"Default", "Export"})
     * @var \DateTime|null
     */
    protected $evaluationDate;

    /**
     * @JMS\Type("int")
     * @JMS\Groups({"Default", "Export"})
     * @var int
     */
    protected $globalStatus = 0;

    public function __construct(
        Pia $pia,
        int $status,
        string $referenceTo,
        string $evaluationComment,
        \DateTime $evaluationDate,
        int $globalStatus
    ) {
        $this->pia = $pia;
        $this->status = $status;
        $this->referenceTo = $referenceTo;
        $this->evaluationComment = $evaluationComment;
        $this->evaluationDate = $evaluationDate;
        $this->globalStatus = $globalStatus;
    }
}
