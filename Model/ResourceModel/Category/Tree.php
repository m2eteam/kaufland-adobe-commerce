<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Category;

class Tree extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const ID_FIELD          = 'id';
    public const COLUMN_STOREFRONT_ID    = 'storefront_id';
    public const COLUMN_CATEGORY_ID = 'category_id';
    public const COLUMN_PARENT_CATEGORY_ID = 'parent_category_id';
    public const COLUMN_TITLE = 'title';

    protected function _construct()
    {
        $this->_init(\M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_CATEGORY_TREE, self::ID_FIELD);
    }
}
