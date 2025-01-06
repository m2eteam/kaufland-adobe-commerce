<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Log;

abstract class AbstractModel extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public const TYPE_INFO = 1;
    public const TYPE_SUCCESS = 2;
    public const TYPE_WARNING = 3;
    public const TYPE_ERROR = 4;

    protected function validateType(int $type): void
    {
        if (!in_array($type, [self::TYPE_INFO, self::TYPE_SUCCESS, self::TYPE_WARNING, self::TYPE_ERROR], true)) {
            throw new \M2E\Kaufland\Model\Exception\Logic("Type '$type' is not valid.");
        }
    }
}
