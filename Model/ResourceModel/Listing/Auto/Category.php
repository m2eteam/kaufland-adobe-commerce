<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Listing\Auto;

class Category extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_GROUP_ID = 'group_id';
    public const COLUMN_CATEGORY_ID = 'category_id';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init(
            \M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_LISTING_AUTO_CATEGORY,
            self::COLUMN_ID
        );
    }
}
