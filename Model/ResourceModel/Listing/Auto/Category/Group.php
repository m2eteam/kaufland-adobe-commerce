<?php

namespace M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category;

class Group extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_LISTING_ID = 'listing_id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_ADDING_MODE = 'adding_mode';
    public const COLUMN_ADDING_ADD_NOT_VISIBLE = 'adding_add_not_visible';
    public const COLUMN_ADDING_TEMPLATE_CATEGORY_ID = 'adding_template_category_id';
    public const COLUMN_DELETING_MODE = 'deleting_mode';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group\CollectionFactory $autoGroupCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\CollectionFactory $autoCategoryCollectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group\CollectionFactory $autoGroupCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\CollectionFactory $autoCategoryCollectionFactory,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->autoGroupCollectionFactory = $autoGroupCollectionFactory;
        $this->autoCategoryCollectionFactory = $autoCategoryCollectionFactory;
    }

    public function _construct()
    {
        $this->_init(
            \M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_LISTING_AUTO_CATEGORY_GROUP,
            self::COLUMN_ID
        );
    }

    public function getCategoriesFromOtherGroups(int $listingId, $groupId = null): array
    {
        $groupCollection = $this->autoGroupCollectionFactory->create();
        $groupCollection->addFieldToFilter(
            sprintf('main_table.%s', self::COLUMN_LISTING_ID),
            ['eq' => $listingId]
        );

        if (!empty($groupId)) {
            $groupCollection->addFieldToFilter(
                sprintf('main_table.%s', self::COLUMN_ID),
                ['neq' => $groupId]
            );
        }

        $groupIds = $groupCollection->getAllIds();
        if (count($groupIds) == 0) {
            return [];
        }

        $collection = $this->autoCategoryCollectionFactory->create();
        $collection
            ->getSelect()
            ->joinInner(
                ['auto_group' => $this->getMainTable()],
                sprintf(
                    'main_table.%s = auto_group.%s',
                    \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_GROUP_ID,
                    self::COLUMN_ID
                ),
                ['group_title' => 'title']
            );

        $collection
            ->getSelect()
            ->where(
                sprintf(
                    'main_table.%s IN (?)',
                    \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_GROUP_ID
                ),
                $groupIds
            );

        $result = [];
        foreach ($collection->getItems() as $item) {
            $result[$item->getData('category_id')] = [
                'id' => $item->getData('group_id'),
                'title' => $item->getData('group_title'),
            ];
        }

        return $result;
    }

    public function isEmpty(int $groupId): bool
    {
        $collection = $this->autoCategoryCollectionFactory->create();
        $collection->addFieldToFilter(
            sprintf(
                'main_table.%s',
                \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_GROUP_ID
            ),
            ['eq' => $groupId]
        );

        return $collection->getSize() === 0;
    }
}
