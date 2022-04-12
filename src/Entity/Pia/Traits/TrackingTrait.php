<?php

namespace PiaApi\Entity\Pia\Traits;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\TrackingLog;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        return new TrackingLog($activity, $user, $this->getEntityClass(), $this->getId());
    }

    /**
     * Returns the tracking log entries for the current object.
     * 
     * @JMS\VirtualProperty
     * @JMS\SerializedName("trackings")
     */
    public function getTrackingLogs()
    {
        $params = ['contentType' => $this->getEntityClass(), 'entityId' => $this->getId()];
        $trackingLogs = $this->entityManager->getRepository(TrackingLog::class)->findBy($params);
        $trackings = [];
        foreach ($trackingLogs as $tracking)
        {
            $trackings[] = [
                $tracking->getOwner()->getProfile()->getFullname(),
                $tracking->getActivity(),
                $tracking->getDate(),
            ];
        }
        return $trackings;
    }

    /**
     * Retrieves entityManager injected by ObjectManagerAware interface.
     */
    public function injectObjectManager(ObjectManager $objectManager, ClassMetadata $classMetadata)
    {
        $this->entityManager = $objectManager;
    }

    public function getEntityClass()
    {
        $classname = get_class($this);
        if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
        return $pos;
    }
}
