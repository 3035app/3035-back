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

class TrackingDescriptor extends AbstractDescriptor
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $activity = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "Export"})
     * 
     * @var string
     */
    protected $owner = '';

    /**
     * @JMS\Type("DateTime")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var \DateTime|null
     */
    protected $date = '';

    public function __construct(
        string $activity,
        string $owner,
        \DateTime $date
    ) {
        $this->activity = $activity;
        $this->owner = $owner;
        $this->date = $date;
    }
}
