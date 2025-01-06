<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Processing;

class ResultHandlerCollection
{
    private const MAP = [
        \M2E\Kaufland\Model\Listing\InventorySync\Processing\ResultHandler::NICK =>
            \M2E\Kaufland\Model\Listing\InventorySync\Processing\ResultHandler::class,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Async\Processing\ResultHandler::NICK =>
            \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Async\Processing\ResultHandler::class,
    ];

    public function has(string $nick): bool
    {
        return isset(self::MAP[$nick]);
    }

    /**
     * @param string $nick
     *
     * @return string result handler class name
     */
    public function get(string $nick): string
    {
        return self::MAP[$nick];
    }
}
