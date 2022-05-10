<?php

namespace PiaApi\Entity\Pia\Traits;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\TrackingLog;

trait TrackingLogTrait
{
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
        if (isset($this->entityManager) && null !== $this->entityManager)
        {
            # request optimised in repository
            $options = ['contentType' => $this->getEntityClass(), 'entityId' => $this->getId()];
            return $this->entityManager->getRepository(TrackingLog::class)->findTrackingsBy($options);
        }
        return [];
    }

    /**
     * Returns the tracking log entries object list for the current object.
     * 
     * @JMS\Exclude()
     */
    public function getTrackingsObjectList()
    {
        if (isset($this->entityManager) && null !== $this->entityManager)
        {
            $options = ['contentType' => $this->getEntityClass(), 'entityId' => $this->getId()];
            return $this->entityManager->getRepository(TrackingLog::class)->findBy($options);
        }
        return [];
    }

    /**
     * Retrieves entityManager injected by ObjectManagerAware interface.
     * not retrieved in creation mode.
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
