<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Entity\Pia;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\Pia;
use PiaApi\Entity\Pia\Traits\CommentedByTrait;
use PiaApi\Entity\Pia\Traits\HasPiaTrait;
use PiaApi\Entity\Pia\Traits\ResourceTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="pia_comment")
 */
class Comment implements Timestampable
{
    use CommentedByTrait, HasPiaTrait, ResourceTrait, TimestampableEntity;

    /**
     * @ORM\ManyToOne(targetEntity="Pia", inversedBy="comments")
     * @JMS\Exclude()
     *
     * @var Pia
     */
    protected $pia;

    /**
     * @ORM\Column(type="text")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="string")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var string
     */
    protected $referenceTo;

    /**
     * @ORM\Column(type="boolean")
     * @JMS\Groups({"Default", "Export"})
     *
     * @var bool
     */
    protected $forMeasure = false;

    public function __construct(Pia $pia, string $description, string $referenceTo, bool $forMeasure, User $user)
    {
        $this->pia = $pia;
        $this->description = $description;
        $this->referenceTo = $referenceTo;
        $this->forMeasure = $forMeasure;
        $this->user = $user;
    }
}
