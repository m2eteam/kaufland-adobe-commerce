<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Listing;

class Other extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_STOREFRONT_ID = 'storefront_id';
    public const COLUMN_UNIT_ID = 'unit_id';
    public const COLUMN_OFFER_ID = 'offer_id';
    public const COLUMN_KAUFLAND_PRODUCT_ID = 'kaufland_product_id';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_HANDLING_TIME = 'hadling_time';
    public const COLUMN_WAREHOUSE_ID = 'warehouse_id';
    public const COLUMN_SHIPPING_GROUP_ID = 'shipping_group_id';
    public const COLUMN_CONDITION = 'condition';
    public const COLUMN_EANS = 'eans';
    public const COLUMN_CURRENCY_CODE = 'currency_code';
    public const COLUMN_PRICE = 'price';
    public const COLUMN_QTY = 'qty';
    public const COLUMN_MAIN_PICTURE = 'main_picture';
    public const COLUMN_CATEGORY_ID = 'category_id';
    public const COLUMN_CATEGORY_TITLE = 'category_title';
    public const COLUMN_FULFILLED_BY_MERCHANT = 'fulfilled_by_merchant';
    public const COLUMN_MOVED_TO_LISTING_PRODUCT_ID = 'moved_to_listing_product_id';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init(
            \M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_LISTING_OTHER,
            self::COLUMN_ID
        );
    }
}
