<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

/**
 * RSSI in french:
 * Responsable de la Sécurité des Systèmes d'Information
 * 
 * Translated in english:
 * Head of Information Systems Security (HSSI)
 * 
 */

namespace PiaApi\Entity\Pia\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait HissOpinionTrait
{
    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var bool
     */
    protected $requestedHissOpinion;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $hissName;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var int
     */
    protected $hissProcessingImplementedStatus;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $hissOpinion = '';

    /**
     * Sets requestedHissOpinion.
     * 
     * @param $requestedHissOpinion bool
     * @return $this
     */
    public function setRequestedHissOpinion(bool $requestedHissOpinion)
    {
        $this->requestedHissOpinion = $requestedHissOpinion;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRequestedHissOpinion(): ?bool
    {
        return $this->requestedHissOpinion;
    }

    /**
     * Sets hissName.
     * 
     * @param $hissName string
     * @return $this
     */
    public function setHissName(string $hissName)
    {
        $this->hissName = $hissName;
        return $this;
    }

    /**
     * @return string
     */
    public function getHissName(): ?string
    {
        return $this->hissName;
    }
}