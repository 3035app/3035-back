<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Entity\Pia;

use PiaApi\Entity\Oauth\User;

interface TrackingInterface
{
    /**
     * Logs a tracking activity entry.
     */
    public function logTrackingActivity(User $user, string $activity);
}
