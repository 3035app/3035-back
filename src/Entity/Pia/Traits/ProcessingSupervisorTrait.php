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

trait ProcessingSupervisorTrait
{
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * 
     * @var User
     */
    protected $redactor;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * 
     * @var User
     */
    protected $dataController;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true)
     * 
     * @var User
     */
    protected $evaluatorPending;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true)
     * 
     * @var User
     */
    protected $dataProtectionOfficerPending;

    private function getSupervisor($obj)
    {
        if (null === $obj) return null;
        return [
            'id' => $obj->getId(),
            'username' => $obj->getUsername()
        ];
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("supervisors")
     * 
     * @return array
     */
    public function getProcessingSupervisors()
    {
        return [
            'redactor' => $this->getSupervisor($this->getRedactor()),
            'data_controller' => $this->getSupervisor($this->getDataController()),
            'evaluator_pending' => $this->getSupervisor($this->getEvaluatorPending()),
            'data_protection_officer_pending' => $this->getSupervisor($this->getDataProtectionOfficerPending())
        ];
    }

    /**
     * @return User
     */
    public function getRedactor(): User
    {
        return $this->redactor;
    }

    /**
     * Sets redactor.
     * @param User $redactor
     * @return $this
     */
    public function setRedactor(User $redactor)
    {
        $this->redactor = $redactor;
        return $this;
    }

    /**
     * @return User
     */
    public function getDataController(): User
    {
        return $this->dataController;
    }

    /**
     * Sets dataController.
     * @param User $dataController
     * @return $this
     */
    public function setDataController(?User $dataController)
    {
        $this->dataController = $dataController;
        return $this;
    }

    /**
     * @return User
     */
    public function getEvaluatorPending(): ?User
    {
        return $this->evaluatorPending;
    }

    /**
     * Sets evaluatorPending.
     * @param User $evaluatorPending
     * @return $this
     */
    public function setEvaluatorPending(?User $evaluatorPending=null)
    {
        $this->evaluatorPending = $evaluatorPending;
        return $this;
    }

    /**
     * @return User
     */
    public function getDataProtectionOfficerPending(): ?User
    {
        return $this->dataProtectionOfficerPending;
    }

    /**
     * Sets dataProtectionOfficerPending.
     * @param User $dataProtectionOfficerPending
     * @return $this
     */
    public function setDataProtectionOfficerPending(?User $dataProtectionOfficerPending=null)
    {
        $this->dataProtectionOfficerPending = $dataProtectionOfficerPending;
        return $this;
    }
}
