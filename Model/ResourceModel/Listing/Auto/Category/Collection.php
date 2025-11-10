<?php

namespace M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category;

use M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category as ResourceCategory;

/**
 * @method \M2E\Kaufland\Model\Listing\Auto\Category getFirstItem()
 * @method \M2E\Kaufland\Model\Listing\Auto\Category[] getItems()
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Listing\Auto\Category::class,
            \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::class
        );
    }

    public function selectCategoryId(): void
    {
        $this->addFieldToSelect(ResourceCategory::COLUMN_CATEGORY_ID);
    }

    public function selectGroupId(): void
    {
        $this->addFieldToSelect(ResourceCategory::COLUMN_GROUP_ID);
    }

    public function whereCategoryIdIn(array $value): void
    {
        $this->getSelect()->where(ResourceCategory::COLUMN_CATEGORY_ID . ' IN (?)', $value);
    }
}
