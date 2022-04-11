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
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\Traits\ResourceTrait;

/**
 * @ORM\Entity(repositoryClass="PiaApi\Repository\TrackingLogRepository")
 * @ORM\Table(name="pia_trackinglog")
 */
class TrackingLog
{
    use ResourceTrait;

    const ACTIVITY_CREATED = 'created';
    const ACTIVITY_LAST_MODIFICATION = 'last-modification';
    const ACTIVITY_EVALUATION_REQUEST = 'evaluation-request';
    const ACTIVITY_EVALUATION = 'evaluation';
    const ACTIVITY_ISSUE_REQUEST = 'issue-request';
    const ACTIVITY_NOTICE_REQUEST = 'notice-issued';
    const ACTIVITY_VALIDATION_REQUEST = 'validation-request';
    const ACTIVITY_VALIDATED = 'validated';
    const ACTIVITY_STORED = 'stored';

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $activity;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true)
     * @JMS\Exclude()
     * 
     * @var User
     */
    protected $owner;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $contentType;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $entityId;

    public function __construct()
    {
    }

    /**
     * Sets activity.
     * @param string $activity
     * @return $this
     */
    public function setActivity(User $activity)
    {
        $this->activity = $activity;
        return $this;
    }

    /**
     * Returns activity.
     *
     * @return string
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Sets owner.
     * @param User $owner
     * @return $this
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return User
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * Returns date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Sets contentType.
     * @param string $contentType
     * @return $this
     */
    public function setContentType(string $contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Returns contentType.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Sets entityId.
     * @param int $entityId
     * @return $this
     */
    public function setEntityId(string $entityId)
    {
        $this->entityId = $entityId;
        return $this;
    }

    /**
     * Returns entityId.
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("owner_id")
     * 
     * @return integer
     */
    public function getOwnerId()
    {
        return $this->getOwner()->getId();
    }

    /**
     * @return string
     **/
    public function __toString()
    {
        $named = $this->getOwner()->getProfile()->Fullname();
        $formatted = $this->getDate()->format("d/m/Y");
        return sprintf('%s by %s on %s', $this->getActivity(), $named, $formatted);
    }
}
