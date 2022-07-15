<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Services;

use PiaApi\Entity\Pia\Processing;
use PiaApi\Entity\Pia\ProcessingDataType;

class ProcessingDataTypeService extends AbstractService
{
    public function getEntityClass(): string
    {
        return ProcessingDataType::class;
    }

    /**
     * 
     * @param string    $name
     * 
     * @return ProcessingDataType
     */
    public function createProcessingDataType(Processing $processing, string $reference): ProcessingDataType
    {
        return new ProcessingDataType($processing, $reference);
    }

    /**
     * 
     * @return ProcessingDataType
     */
    public function create(Processing $processing, string $reference, string $data, string $retentionPeriod, bool $sensitive): ProcessingDataType
    {
        $processingDataType = $this->createProcessingDataType($processing, $reference);
        $processingDataType->setData($data);
        $processingDataType->setRetentionPeriod($retentionPeriod);
        $processingDataType->setSensitive($sensitive);
        return $processingDataType;
    }
}
