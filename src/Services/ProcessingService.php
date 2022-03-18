<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Services;

use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\Folder;
use PiaApi\Entity\Pia\Processing;

class ProcessingService extends AbstractService
{
    public function getEntityClass(): string
    {
        return Processing::class;
    }

    /**
     * @param string $name
     * @param Folder $folder
     * @param User $redactor
     * @param User $dataController
     * @param User $evaluatorPending
     * @param User $dataProtectionOfficerPending
     *
     * @return Processing
     */
    public function createProcessing(
        string $name,
        Folder $folder,
        User $redactor,
        User $dataController,
        User $evaluatorPending=null,
        User $dataProtectionOfficerPending=null
        ): Processing
    {
        return new Processing($name, $folder, $redactor, $dataController, $evaluatorPending, $dataProtectionOfficerPending);
    }
}
