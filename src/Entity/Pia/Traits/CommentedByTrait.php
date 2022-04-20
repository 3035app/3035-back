<?php

/*
 * Copyright (C) 2015-2018 Libre Informatique
 *
 * This file is licensed under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace PiaApi\Entity\Pia\Traits;

use JMS\Serializer\Annotation as JMS;
use PiaApi\Entity\Oauth\User;

trait CommentedByTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="PiaApi\Entity\Oauth\User", inversedBy="comments")
     * @ORM\JoinColumn(nullable=true)
     * @JMS\Groups({"Default", "Export"})
     * @JMS\Exclude()
     *
     * @var User
     */
    protected $user;

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("commentedBy")
     * 
     * @return array
     */
    public function getCommentedBy()
    {
        if (null == $this->getUser()) {
            return [];
        }
        return [
            'firstName' => $this->getUser()->getProfile()->getFirstName(),
            'lastName' => $this->getUser()->getProfile()->getLastName(),
            'roles' => $this->getUser()->getRoles(),
        ];
    }
}
