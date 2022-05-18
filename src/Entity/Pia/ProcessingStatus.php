<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Entity\Pia;

class ProcessingStatus extends AbstractStatus
{
    const STATUS_DOING = 0;
    const STATUS_UNDER_EVALUATION = 1;
    const STATUS_EVALUATED = 2;
    const STATUS_UNDER_VALIDATION = 3;
    const STATUS_VALIDATED = 4;
    const STATUS_ARCHIVED = 5;

    protected static $statusNames = [
        self::STATUS_DOING              => 'STATUS_DOING',
        self::STATUS_UNDER_EVALUATION   => 'STATUS_UNDER_EVALUATION',
        self::STATUS_EVALUATED          => 'STATUS_EVALUATED',
        self::STATUS_UNDER_VALIDATION   => 'STATUS_UNDER_VALIDATION',
        self::STATUS_VALIDATED          => 'STATUS_VALIDATED',
        self::STATUS_ARCHIVED           => 'STATUS_ARCHIVED',
    ];
}
