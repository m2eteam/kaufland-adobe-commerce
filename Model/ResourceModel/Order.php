<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel;

class Order extends ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_MAGENTO_ORDER_ID = 'magento_order_id';

    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_RESERVATION_STATE = 'reservation_state';
    public const COLUMN_SHIPPING_DETAILS = 'shipping_details';
    public const COLUMN_BILLING_DETAILS = 'billing_details';
    public const COLUMN_MAGENTO_ORDER_CREATION_FAILURE = 'magento_order_creation_failure';
    public const COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT = 'magento_order_creation_fails_count';
    public const COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE = 'magento_order_creation_latest_attempt_date';

    public function _construct(): void
    {
        $this->_init(\M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_ORDER, self::COLUMN_ID);
    }
}
