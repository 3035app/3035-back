<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Tests\unit\Processing;

trait _FixturesTrait
{
    protected $data = [
        'folder' => [
            'name' => 'test-root',
        ],
        'processing' => [
            'name' => 'test-processing',
        ],
        'pia' => [
            'name' => 'test-pia',
        ],
        'processing_data_type' => [
            'name' => 'test-processing-data-type',
        ],
    ];
}
