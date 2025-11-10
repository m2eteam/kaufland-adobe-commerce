<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Actions\CategoryProductRelationProcessor;

class Repository
{
    private \Magento\Catalog\Model\ResourceModel\Product\Website $productWebsiteResource;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\CollectionFactory $autoCategoryCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group $categoryGroupResource;
    private \M2E\Kaufland\Model\ResourceModel\Listing $listingResource;
    private \M2E\Kaufland\Model\ResourceModel\Product $listingProductResource;
    private \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Website $productWebsiteResource,
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\CollectionFactory $autoCategoryCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group $categoryGroupResource,
        \M2E\Kaufland\Model\ResourceModel\Listing $listingResource,
        \M2E\Kaufland\Model\ResourceModel\Product $listingProductResource,
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory
    ) {
        $this->productWebsiteResource = $productWebsiteResource;
        $this->autoCategoryCollectionFactory = $autoCategoryCollectionFactory;
        $this->categoryGroupResource = $categoryGroupResource;
        $this->listingResource = $listingResource;
        $this->listingProductResource = $listingProductResource;
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
    }

    public function isExistsAutoActionsForCategory(int $categoryId): bool
    {
        $collection = $this->autoCategoryCollectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_CATEGORY_ID,
            $categoryId
        );

        return (bool)$collection->getSize();
    }

    /**
     * @return array<int, int[]>
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function retrieveCatalogWebsiteProductsIds(array $productIds): array
    {
        $select = $this->productWebsiteResource->getConnection()->select()->from(
            $this->productWebsiteResource->getMainTable(),
            ['website_id', 'product_id']
        )->where(
            'product_id IN (?)',
            $productIds
        );

        $rowSet = $this->productWebsiteResource->getConnection()->fetchAll($select);

        $result = [];
        foreach ($rowSet as $row) {
            $result[(int)$row['website_id']][] = (int)$row['product_id'];
        }

        return $result;
    }

    /**
     * @return int[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findListingIdsWithEnabledRuleAddedToCategory(int $categoryId, array $storeIds): array
    {
        return $this->getListingIdsWithEnabledCategoryRules(
            'adding_mode',
            $categoryId,
            $storeIds
        );
    }

    /**
     * @return int[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findListingIdsWithEnabledRuleRemovedFromCategory(int $categoryId, array $storeIds): array
    {
        return $this->getListingIdsWithEnabledCategoryRules(
            'deleting_mode',
            $categoryId,
            $storeIds
        );
    }

    /**
     * @return int[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getListingIdsWithEnabledCategoryRules(string $mode, int $categoryId, array $storeIds): array
    {
        $collection = $this->autoCategoryCollectionFactory->create();
        $collection
            ->getSelect()
            ->distinct();
        $collection->join(
            ['category_group' => $this->categoryGroupResource->getMainTable()],
            sprintf(
                'main_table.%s = category_group.%s',
                \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_GROUP_ID,
                \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group::COLUMN_ID
            )
        );
        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->join(
            ['listing' => $this->listingResource->getMainTable()],
            sprintf(
                'category_group.%s = listing.%s',
                \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group::COLUMN_LISTING_ID,
                \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_ID
            ),
            ['listing_id' => \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_ID]
        );
        $collection->addFieldToFilter('category_id', $categoryId);
        $collection->addFieldToFilter('store_id', ['in' => $storeIds]);
        $collection->addFieldToFilter($mode, ['neq' => 0]);

        return array_map(function (\Magento\Framework\DataObject $dataObject) {
            return (int)$dataObject->getDataByKey('listing_id');
        }, $collection->getItems());
    }

    /**
     * @return \Magento\Catalog\Model\Product[]
     */
    public function findProductsThatNotInListings(array $productIds, array $listingIds): array
    {
        $productCollection = $this->magentoProductCollectionFactory->create();
        $productCollection->addFieldToFilter('entity_id', ['in' => $productIds]);

        $listingProductCollection = $this->listingCollectionFactory->create();
        $listingProductSelect = $listingProductCollection->getSelect();
        $listingProductSelect->where(
            sprintf(
                'main_table.%s = e.entity_id',
                \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_MAGENTO_PRODUCT_ID
            )
        );
        $listingProductSelect->where(
            sprintf(
                'main_table.%s IN (?)',
                \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_LISTING_ID
            ),
            $listingIds
        );

        $productCollection->getSelect()->where('NOT EXISTS (?)', $listingProductSelect);

        return $productCollection->getItems();
    }

    /**
     * @return \Magento\Catalog\Model\Product[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findProductsThatInListings($productIds, array $listingIds): array
    {
        $productCollection = $this->magentoProductCollectionFactory->create();
        $select = $productCollection->getSelect();
        $select->join(
            ['lp' => $this->listingProductResource->getMainTable()],
            sprintf(
                'lp.%s = e.entity_id',
                \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_MAGENTO_PRODUCT_ID
            )
        );
        $select
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('e.*');
        $select->where(
            sprintf(
                'lp.%s IN (?)',
                \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_MAGENTO_PRODUCT_ID
            ),
            $productIds
        );
        $select->where(
            sprintf(
                'lp.%s IN (?)',
                \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_LISTING_ID
            ),
            $listingIds
        );

        return $productCollection->getItems();
    }
}
