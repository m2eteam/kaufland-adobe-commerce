<?php

namespace M2E\Kaufland\Model\ResourceModel\Lock\Item;

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
