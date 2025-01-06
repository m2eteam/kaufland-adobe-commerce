<?php

namespace M2E\Kaufland\Model\ResourceModel\Listing;

class Log extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_STOREFRONT_ID = 'storefront_id';
    public const COLUMN_LISTING_ID = 'listing_id';
    public const COLUMN_PRODUCT_ID = 'product_id';
    public const COLUMN_LISTING_PRODUCT_ID = 'listing_product_id';
    public const COLUMN_LISTING_TITLE = 'listing_title';
    public const COLUMN_PRODUCT_TITLE = 'product_title';
    public const COLUMN_ACTION_ID = 'action_id';
    public const COLUMN_ACTION = 'action';
    public const COLUMN_INITIATOR = 'initiator';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_DESCRIPTION = 'description';
    public const COLUMN_ADDITIONAL_DATA = 'additional_data';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct(): void
    {
        $this->_init(\M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_LISTING_LOG, 'id');
    }
}
