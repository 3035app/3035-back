<?php

namespace PiaApi\Entity\Pia\Traits;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\TrackingLog;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

    public static function getEvaluationStates(): array
    {
        return [
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
    public function getEvaluationStateRequest($request, $state='evaluation_state'): string
    {
        return $this->_getEvaluationState($request->get($state));
    }

    /**
     * @param $state int
     * @return bool
     */
    public function hasState($state): bool {
        if (array_key_exists($state, static::getStateNames())) {
            return true;
        }
        return false;
    }

    /**
     * @param $state int
     * @return string
     */
    public function getStateName($state): string {
        return static::getStateNames()[$state];
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
