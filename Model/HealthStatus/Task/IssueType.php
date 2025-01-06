<?php

namespace M2E\Kaufland\Model\HealthStatus\Task;

/**
 * Class \M2E\Kaufland\Model\HealthStatus\Task\IssueType
 */
abstract class IssueType extends AbstractModel
{
    public const TYPE = 'issue';

    //########################################

    public function getType()
    {
        return self::TYPE;
    }

    public function mustBeShownIfSuccess()
    {
        return false;
    }

    //########################################
}
