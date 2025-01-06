<?php

namespace M2E\Kaufland\Model\ResourceModel\Order\Note;

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public const ORDER_ID_FIELD = 'order_id';

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Order\Note::class,
            \M2E\Kaufland\Model\ResourceModel\Order\Note::class
        );
    }

    /**
     * @return \M2E\Kaufland\Model\Order\Note[]
     */
    public function getItems()
    {
        /** @var \M2E\Kaufland\Model\Order\Note[] $items */
        $items = parent::getItems();

        return $items;
    }
}
