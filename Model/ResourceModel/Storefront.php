<?php

namespace M2E\Kaufland\Model\ResourceModel;

class Storefront extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_STOREFRONT_CODE = 'storefront_code';
    public const COLUMN_ORDER_LAST_SYNC = 'orders_last_synchronization';
    public const COLUMN_INVENTORY_LAST_SYNC = 'inventory_last_synchronization';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct()
    {
        $this->_init(\M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_STOREFRONT, self::COLUMN_ID);
    }

    public function loadByCode(\M2E\Kaufland\Model\Storefront $object, string $code): Storefront
    {
        return $this->load($object, $code, 'storefront_code');
    }
}
