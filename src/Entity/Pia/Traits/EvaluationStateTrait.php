<?php

namespace PiaApi\Entity\Pia\Traits;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\TrackingLog;

trait EvaluationStateTrait
{
    public static function getStateNames(): array
    {
        return [
            static::EVALUATION_STATE_NONE => "Aucune évaluation",
            static::EVALUATION_STATE_TO_CORRECT => "À corriger",
            static::EVALUATION_STATE_IMPROVABLE => "Améliorable",
            static::EVALUATION_STATE_ACCEPTABLE => "Acceptable",
        ];
    }

    /**
     * @return string
     */
    public function getEvaluationStateNamed(): string
    {
        return $this->_getEvaluationState($this->getEvaluationState());
    }

    /**
     * @return string
     */
    public function getEvaluationStateRequest($request): string
    {
        return $this->_getEvaluationState($request->get('evaluation_state'));
    }

    /**
     * @param $state int
     * @return bool
     */
    public function hasState($state): bool {
        if (array_key_exists($state, self::getStateNames())) {
            return true;
        }
        return false;
    }

    /**
     * @param $state int
     * @return string
     */
    public function getStateName($state): string {
        return self::getStateNames()[$state];
    }

    /**
     * @param $mixed
     * @return string
     */
    public function _getEvaluationState($mixed): string
    {
        if ($this->hasState($mixed)) {
            return $this->getStateName($mixed);
        }
        return $this->getStateName(static::EVALUATION_STATE_NONE);
    }
}
