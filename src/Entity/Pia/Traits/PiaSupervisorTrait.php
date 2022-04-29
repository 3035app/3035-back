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
use PiaApi\Entity\Oauth\User;

trait PiaSupervisorTrait
{
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @JMS\Exclude()
     * 
     * @var User
     */
    protected $evaluator;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @JMS\Exclude()
     * 
     * @var User
     */
    protected $dataProtectionOfficer;

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("supervisors")
     * 
     * @return array
     */
    public function getPiaSupervisors()
    {
        return [
            'evaluator' => $this->getSupervisor($this->getEvaluator()),
            'data_protection_officer' => $this->getSupervisor($this->getDataProtectionOfficer()),
        ];
    }

    /**
     * @return User
     */
    public function getEvaluator(): User
    {
        return $this->evaluator;
    }

    /**
     * Sets evaluator.
     * @param User $evaluator
     * @return $this
     */
    public function setEvaluator(?User $evaluator=null)
    {
        $this->evaluator = $evaluator;
        return $this;
    }

    /**
     * @return User
     */
    public function getDataProtectionOfficer(): User
    {
        return $this->dataProtectionOfficer;
    }

    /**
     * Sets dataProtectionOfficer.
     * @param User $dataProtectionOfficer
     * @return $this
     */
    public function setDataProtectionOfficer(?User $dataProtectionOfficer=null)
    {
        $this->dataProtectionOfficer = $dataProtectionOfficer;
        return $this;
    }

    private function getSupervisor($obj)
    {
        return ['id' => $obj->getId(), 'name' => $obj->getProfile()->getFullname()];
    }
}
