<?php

namespace M2E\Kaufland\Model\ResourceModel\Order\Item;

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Order\Item::class,
            \M2E\Kaufland\Model\ResourceModel\Order\Item::class
        );
    }

    /**
     * @return \M2E\Kaufland\Model\Order\Item[]
     */
    public function getItems()
    {
        /** @var \M2E\Kaufland\Model\Order\Item[] $items */
        $items = parent::getItems();

        return $items;
    }
}
