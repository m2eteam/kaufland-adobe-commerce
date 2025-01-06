<?php

namespace M2E\Kaufland\Model\ResourceModel\Listing;

/**
 * @method \M2E\Kaufland\Model\Listing[] getItems()
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    private \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Helper\Module\Database\Structure $moduleDatabaseStructure,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $activeRecordFactory,
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Listing::class,
            \M2E\Kaufland\Model\ResourceModel\Listing::class
        );
    }

    public function addProductsTotalCount(): self
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToSelect(\M2E\Kaufland\Model\ResourceModel\Product::COLUMN_LISTING_ID);
        $collection->addExpressionFieldToSelect(
            'products_total_count',
            'COUNT({{id}})',
            ['id' => \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_ID]
        );
        $collection->getSelect()->group(\M2E\Kaufland\Model\ResourceModel\Product::COLUMN_LISTING_ID);

        $this->getSelect()->joinLeft(
            ['t' => $collection->getSelect()],
            'main_table.id=t.listing_id',
            [
                'products_total_count' => 'products_total_count',
            ]
        );

        return $this;
    }
}
