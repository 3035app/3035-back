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
     * 
     * @var User
     */
    protected $evaluatorPending;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * 
     * @var User
     */
    protected $dataProtectionOfficerPending;

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
    public function setRedactor(User $redactor): void
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
    public function setDataController(?User $dataController=null): void
    {
        $this->dataController = $dataController;
        return $this;
    }

    /**
     * @return User
     */
    public function getEvaluatorPending(): User
    {
        return $this->evaluatorPending;
    }

    /**
     * Sets evaluatorPending.
     * @param User $evaluatorPending
     * @return $this
     */
    public function setEvaluatorPending(?User $evaluatorPending=null): void
    {
        $this->evaluatorPending = $evaluatorPending;
        return $this;
    }

    /**
     * @return User
     */
    public function getDataProtectionOfficerPending(): User
    {
        return $this->dataProtectionOfficerPending;
    }

    /**
     * Sets dataProtectionOfficerPending.
     * @param User $dataProtectionOfficerPending
     * @return $this
     */
    public function setDataProtectionOfficerPending(?User $dataProtectionOfficerPending=null): void
    {
        $this->dataProtectionOfficerPending = $dataProtectionOfficerPending;
        return $this;
    }
}
