<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Entity\Pia\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait ProcessingSupervisorTrait
{
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @JMS\Exclude()
     * 
     * @var User
     */
    protected $sAuthor;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @JMS\Exclude()
     * 
     * @var User
     */
    protected $sDesignatedController;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @JMS\Exclude()
     * 
     * @var User
     */
    protected $sDataProtectionOfficer;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @JMS\Exclude()
     * 
     * @var User
     */
    protected $sDataController;

    /**
     * @return User
     */
    public function getSAuthor(): User
    {
        return $this->sAuthor;
    }

    /**
     * @param User $sAuthor
     */
    public function setSAuthor(User $sAuthor): void
    {
        $this->sAuthor = $sAuthor;
    }

    /**
     * @return User
     */
    public function getSDesignatedController(): User
    {
        return $this->sDesignatedController;
    }

    /**
     * @param User $sDesignatedController
     */
    public function setSDesignatedController(?User $sDesignatedController=null): void
    {
        $this->sDesignatedController = $sDesignatedController;
    }

    /**
     * @return User
     */
    public function getSDataProtectionOfficer(): User
    {
        return $this->sDataProtectionOfficer;
    }

    /**
     * @param User $sDataProtectionOfficer
     */
    public function setSDataProtectionOfficer(?User $sDataProtectionOfficer=null): void
    {
        $this->sDataProtectionOfficer = $sDataProtectionOfficer;
    }

    /**
     * @return User
     */
    public function getSDataController(): User
    {
        return $this->sDataController;
    }

    /**
     * @param User $sDataController
     */
    public function setSDataController(?User $sDataController=null): void
    {
        $this->sDataController = $sDataController;
    }
}
