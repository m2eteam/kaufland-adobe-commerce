<?php

namespace M2E\Kaufland\Model\HealthStatus\Task;

/**
 * Class \M2E\Kaufland\Model\HealthStatus\Task\AbstractModel
 */
abstract class AbstractModel extends \M2E\Kaufland\Model\AbstractModel
{
    //########################################

    public function mustBeShownIfSuccess()
    {
        return true;
    }

    //########################################

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @return Result
     */
    abstract public function process();

    //########################################
}
