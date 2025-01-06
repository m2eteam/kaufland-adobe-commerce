<?php

namespace M2E\Kaufland\Model\ResourceModel\Order;

class Note extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const ORDER_ID_FIELD = 'order_id';

    public function _construct()
    {
        $this->_init(\M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_ORDER_NOTE, 'id');
    }
}
