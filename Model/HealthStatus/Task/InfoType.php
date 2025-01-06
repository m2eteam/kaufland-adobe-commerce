<?php

namespace M2E\Kaufland\Model\HealthStatus\Task;

/**
 * Class \M2E\Kaufland\Model\HealthStatus\Task\InfoType
 */
abstract class InfoType extends AbstractModel
{
    public const TYPE = 'info';

    //########################################

    public function getType()
    {
        return self::TYPE;
    }

    //########################################
}
