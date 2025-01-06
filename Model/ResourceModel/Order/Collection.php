<?php

namespace M2E\Kaufland\Model\ResourceModel\Order;

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Order::class,
            \M2E\Kaufland\Model\ResourceModel\Order::class
        );
    }
}
