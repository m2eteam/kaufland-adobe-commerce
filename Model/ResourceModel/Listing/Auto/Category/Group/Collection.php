<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group;

use M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group as GroupResource;

/**
 * @method \M2E\Kaufland\Model\Listing\Auto\Category\Group getFirstItem()
 * @method \M2E\Kaufland\Model\Listing\Auto\Category\Group[] getItems()
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        $this->_init(
            \M2E\Kaufland\Model\Listing\Auto\Category\Group::class,
            \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group::class
        );
    }

    /**
     * @return void
     */
    public function whereAddingOrDeletingModeEnabled(): void
    {
        $addingField = GroupResource::COLUMN_ADDING_MODE;
        $addingModeNone = \M2E\Kaufland\Model\Listing::ADDING_MODE_NONE;

        $deletingField = GroupResource::COLUMN_DELETING_MODE;
        $deletingModeNone = \M2E\Kaufland\Model\Listing::DELETING_MODE_NONE;

        $this->getSelect()->where("$addingField <> $addingModeNone OR $deletingField <> $deletingModeNone");
    }
}
