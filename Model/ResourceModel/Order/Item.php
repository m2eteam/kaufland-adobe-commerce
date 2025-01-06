<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Order;

class Item extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_PRODUCT_ID = 'product_id';
    public const COLUMN_ORDER_ID = 'order_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_KAUFLAND_OFFER_ID = 'kaufland_offer_id';
    public const COLUMN_QTY_PURCHASED = 'qty_purchased';

    public function _construct(): void
    {
        $this->_init(\M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_ORDER_ITEM, self::COLUMN_ID);
    }
}
