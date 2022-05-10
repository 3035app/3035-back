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

class CommentDescriptor extends AbstractDescriptor
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $description = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $referenceTo = '';

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var bool
     */
    protected $forMeasure = false;

    /**
     * @JMS\Type("DateTime")
     * @JMS\Groups({"Export"})
     *
     * @var \DateTime|null
     */
    protected $createdAt = '';

    /**
     * @JMS\Type("DateTime")
     * @JMS\Groups({"Export"})
     *
     * @var \DateTime|null
     */
    protected $updatedAt = '';

    /**
     * @JMS\Type("array")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var array
     */
    protected $commented_by = [];

    public function __construct(
        string $description,
        string $referenceTo,
        bool $forMeasure,
        \DateTime $createdAt,
        \DateTime $updatedAt,
        array $commented_by
    ) {
        $this->description = $description;
        $this->referenceTo = $referenceTo;
        $this->forMeasure = $forMeasure;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->commented_by = $commented_by;
    }
}
