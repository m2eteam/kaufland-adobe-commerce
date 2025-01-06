<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Listing\Wizard;

class Product extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_WIZARD_ID = 'wizard_id';
    public const COLUMN_UNMANAGED_PRODUCT_ID = 'unmanaged_product_id';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_KAUFLAND_PRODUCT_ID = 'kaufland_product_id';
    public const COLUMN_PRODUCT_ID_SEARCH_STATUS = 'product_id_search_status';
    public const COLUMN_CATEGORY_ID = 'category_id';
    public const COLUMN_CATEGORY_TITLE = 'category_title';
    public const COLUMN_IS_PROCESSED = 'is_processed';

    protected function _construct(): void
    {
        $this->_init(\M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_LISTING_WIZARD_PRODUCT, self::COLUMN_ID);
    }
}
