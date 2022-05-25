<?php

namespace PiaApi\Entity\Pia\Traits;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use PiaApi\Entity\Oauth\User;
use PiaApi\Entity\Pia\TrackingLog;

trait EvaluationStateTrait
{
    protected static $stateNames = [
        self::EVALUATION_STATE_NONE => "Aucune évaluation",
        self::EVALUATION_STATE_TO_CORRECT => "À corriger",
        self::EVALUATION_STATE_IMPROVABLE => "Améliorable",
        self::EVALUATION_STATE_ACCEPTABLE => "Acceptable",
    ];

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
     * @param $mixed |
     * @return string
     */
    public function _getEvaluationState($mixed): string
    {
        if (array_key_exists($mixed, self::$stateNames)) {
            return self::$stateNames[$mixed];
        }
        return self::$stateNames[self::EVALUATION_STATE_NONE];
    }
}
