<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Lock\Item;

/**
 * @method \M2E\Kaufland\Model\Lock\Item getFirstItem()
 * @method \M2E\Kaufland\Model\Lock\Item[] getItems()
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(
            \M2E\Kaufland\Model\Lock\Item::class,
            \M2E\Kaufland\Model\ResourceModel\Lock\Item::class
        );
    }
}
