<?php

namespace PiaApi\Entity\Pia\Traits;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use PiaApi\Entity\Oauth\User;

/**
 * A trait to add activity tracking to a model object.
 * Timestampable Trait, usable with PHP >= 5.4.
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
trait TrackingTrait
{
    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * Sets createdAt.
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Returns createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets updatedAt.
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Returns updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Logs a tracking activity entry.
     */
    public function logTrackingActivity(User $user, string $activity)
    {
        assert(null != $this->getId(), 'entity must have been saved before logging an activity.');
        # $tl = new TrackingLog();
        # $tl->setContentType(/* convert $this to string */);
        /*
        $tl->setActivity($activity);
        $tl->setOwner($user);
        $this->manager->persist($tl);
        $this->manager->flush();
        */
    }

    /**
     * Returns the tracking log entries for the current object.
     */
    public function getTrackingLogs()
    {}

    /**
     * @ORM\PreUpdate
     */
    public function preUpdateEvent()
    {
        // print_r($this->__toString());
    }
}
