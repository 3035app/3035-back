<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Entity\Pia\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use PiaApi\Entity\Oauth\User;

trait ProcessingSupervisorTrait
{
    /**
     * many processings have many redactors.
     * @ORM\ManyToMany(targetEntity=User::class)
     * @ORM\JoinTable(
     *      name="pia_processings_redactors",
     *      joinColumns={@ORM\JoinColumn(name="processing_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="redactor_id", referencedColumnName="id")}
     * )
     * @JMS\Exclude()
     *
     * @var Collection
     */
    protected $redactors;

    /**
     * @deprecated TO BE REMOVED!
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true)
     * @JMS\Exclude()
     * 
     * @var User
     */
    protected $redactor;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @JMS\Exclude()
     * 
     * @var User
     */
    protected $dataController;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true)
     * @JMS\Exclude()
     * 
     * @var User
     */
    protected $evaluatorPending;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true)
     * @JMS\Exclude()
     * 
     * @var User
     */
    protected $dataProtectionOfficerPending;

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("supervisors")
     * 
     * @return array
     */
    public function getProcessingSupervisors()
    {
        return [
            'redactors_id' => $this->getSupervisors($this->getRedactors()),
            'data_controller_id' => $this->getSupervisor($this->getDataController()),
            'evaluator_pending_id' => $this->getSupervisor($this->getEvaluatorPending()),
            'data_protection_officer_pending_id' => $this->getSupervisor($this->getDataProtectionOfficerPending())
        ];
    }

    /**
     * @deprecated TO BE REMOVED!
     * @return User
     */
    public function getRedactor(): ?User
    {
        return $this->redactor;
    }

    /**
     * Sets redactor.
     * @deprecated TO BE REMOVED!
     * @param User $redactor
     * @return $this
     */
    public function setRedactor(User $redactor)
    {
        $this->redactor = $redactor;
        return $this;
    }

    /**
     * @param User $redactor
     */
    public function addRedactor(User $redactor): void
    {
        if (null !== $this->redactors && !$this->redactors->contains($redactor)) {
            $this->redactors->add($redactor);
        }
    }

    /**
     * @param User $redactor
     */
    public function removeRedactor(User $redactor): void
    {
        if (null !== $this->redactors && $this->redactors->contains($redactor)) {
            $this->redactors->removeElement($redactor);
        }
    }

    public function removeAllRedactors(): void
    {
        if (null !== $this->redactors) {
            foreach ($this->redactors as $redactor) {
                $this->redactors->removeElement($redactor);
            }
        }
    }

    /**
     * @return Collection
     */
    public function getRedactors(): ?Collection
    {
        return $this->redactors;
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
        if (0 < $this->getPiasCount()) {
            # FIXME for now, every pia from the same processing has the same evaluator.
            foreach ($this->getPias() as $pia) {
                $pia->setEvaluator($evaluatorPending);
            }
        }
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
        if (0 < $this->getPiasCount()) {
            # FIXME for now, every pia from the same processing has the same dpo.
            foreach ($this->getPias() as $pia) {
                $pia->setDataProtectionOfficer($dataProtectionOfficerPending);
            }
        }
        return $this;
    }

    private function getSupervisors($supervisors)
    {
        if (null === $supervisors) {
            return [];
        }
        $ids = [];
        foreach ($supervisors as $supervisor) {
            array_push($ids, $supervisor->getId());
        }
        return $ids;
    }

    private function getSupervisor($obj)
    {
        if (null === $obj) {
            return null;
        }
        return $obj->getId();
    }
}
