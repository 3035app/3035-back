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

class ProcessingCommentDescriptor extends AbstractDescriptor
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $content = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $field = '';

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
        string $content,
        string $field,
        \DateTime $createdAt,
        \DateTime $updatedAt,
        array $commented_by
    ) {
        $this->content = $content;
        $this->field = $field;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->commented_by = $commented_by;
    }
}
