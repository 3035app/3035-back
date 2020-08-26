<?php

/*
 * Copyright (C) 2015-2019 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Search model
 */
class SearchModel
{

    /**
     * Should not be null, pass empty string either
     *
     * @JMS\Groups({"Default"})
     *
     * @var string
     */
    protected $value;


    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return SearchModel
     */
    public function setValue(string $value): SearchModel
    {
        $this->value = $value;
        return $this;
    }
}
