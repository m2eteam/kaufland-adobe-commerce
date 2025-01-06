<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action;

use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type as ActionType;

class DefinitionsCollection
{
    public const ACTION_UNIT_LIST = 'unit_list';
    public const ACTION_PRODUCT_LIST = 'product_list';
    public const ACTION_UNIT_REVISE = 'unit_revise';
    public const ACTION_PRODUCT_REVISE = 'product_revise';
    public const ACTION_UNIT_STOP = 'unit_stop';
    public const ACTION_UNIT_DELETE = 'unit_delete';
    public const ACTION_UNIT_RELIST = 'unit_relist';

    private const MAP = [
        self::ACTION_PRODUCT_LIST => [
            'start' => ActionType\ListProduct\ProcessStart::class,
            'end' => ActionType\ListProduct\ProcessEnd::class,
        ],
        self::ACTION_PRODUCT_REVISE => [
            'start' => ActionType\ReviseProduct\ProcessStart::class,
            'end' => ActionType\ReviseProduct\ProcessEnd::class,
        ],
    ];

    public function has(string $nick): bool
    {
        return isset(self::MAP[$nick]);
    }

    public function getStart(string $nick): string
    {
        return self::MAP[$nick]['start'];
    }

    public function getEnd(string $nick): string
    {
        return self::MAP[$nick]['end'];
    }
}
