<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\AffectedProduct;

use M2E\Kaufland\Model\ResourceModel\Product as ListingProductResource;

class Finder
{
    private \M2E\Kaufland\Model\ResourceModel\Product $listingProductResource;
    private \M2E\Kaufland\Model\ResourceModel\Listing $listingResource;
    private \M2E\Kaufland\Helper\Data\Cache\Runtime $runtimeCache;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Product $listingProductResource,
        \M2E\Kaufland\Model\ResourceModel\Listing $listingResource,
        \M2E\Kaufland\Helper\Data\Cache\Runtime $runtimeCache,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->listingProductResource = $listingProductResource;
        $this->listingResource = $listingResource;
        $this->runtimeCache = $runtimeCache;
        $this->resourceConnection = $resourceConnection;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function find(
        int $magentoProductId,
        array $listingFilters = [],
        array $listingProductFilters = []
    ): \M2E\Kaufland\Model\Product\AffectedProduct\Collection {
        $filters = [$listingFilters, $listingProductFilters];
        $cacheKey = __METHOD__ . $magentoProductId . sha1(\M2E\Core\Helper\Json::encode($filters));
        $cacheValue = $this->runtimeCache->getValue($cacheKey);

        if ($cacheValue !== null) {
            return $cacheValue;
        }

        $simpleProductsSelect = $this->getSimpleProductSelect($magentoProductId);
        $simpleProductsSelect = $this->applyListingFilters($simpleProductsSelect, $listingFilters);
        $simpleProductsSelect = $this->applyListingProductFilters($simpleProductsSelect, $listingProductFilters);

        /** @var array{array{product_id: string}} $affectedDataLines */
        $affectedDataLines = $this->resourceConnection
            ->getConnection()
            ->select()
            ->query()
            ->fetchAll();

        $listingProductsSortedById = $this->getListingProducts(
            $this->getUniqueProductIds($affectedDataLines)
        );

        $resultCollection = new \M2E\Kaufland\Model\Product\AffectedProduct\Collection();

        foreach ($affectedDataLines as $affectedId) {
            $affectedProduct = $listingProductsSortedById[$affectedId['product_id']];

            $resultCollection->addResult(
                new \M2E\Kaufland\Model\Product\AffectedProduct\Product(
                    $affectedProduct
                )
            );
        }

        $this->runtimeCache->setValue($cacheKey, $resultCollection);

        return $resultCollection;
    }

    private function applyListingProductFilters(
        \Magento\Framework\DB\Select $select,
        array $listingProductFilters
    ): \Magento\Framework\DB\Select {
        if (empty($listingProductFilters)) {
            return $select;
        }

        foreach ($listingProductFilters as $column => $value) {
            $condition = is_array($value)
                ? sprintf('listing_product.%s IN(?)', $column)
                : sprintf('listing_product.%s = ?', $column);

            $select->where($condition, $value);
        }

        return $select;
    }

    private function applyListingFilters(
        \Magento\Framework\DB\Select $select,
        array $listingFilters
    ): \Magento\Framework\DB\Select {
        if (empty($listingFilters)) {
            return $select;
        }

        $select->join(
            ['listing' => $this->listingResource->getMainTable()],
            sprintf(
                'listing.%s = listing_product.%s',
                \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_ID,
                \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_LISTING_ID,
            ),
            [],
        );

        foreach ($listingFilters as $column => $value) {
            $condition = is_array($value)
                ? sprintf('listing.%s IN(?)', $column)
                : sprintf('listing.%s = ?', $column);

            $select->where($condition, $value);
        }

        return $select;
    }

    /**
     * @return \M2E\Kaufland\Model\Product[]
     */
    private function getListingProducts(array $listingProductIds): array
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(ListingProductResource::COLUMN_ID, ['in' => $listingProductIds]);

        $result = [];
        foreach ($collection->getItems() as $item) {
            $result[$item->getId()] = $item;
        }

        return $result;
    }

    private function getSimpleProductSelect(int $magentoProductId): \Magento\Framework\DB\Select
    {
        $select = $this->resourceConnection->getConnection()->select();

        $select->distinct();
        $select->from(
            ['listing_product' => $this->listingProductResource->getMainTable()],
            [
                'product_id' => ListingProductResource::COLUMN_ID,

            ],
        );
        $select->where(
            sprintf('listing_product.%s = ?', ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID),
            $magentoProductId,
        );

        return $select;
    }

    private function getUniqueProductIds(array $affectedDataLines): array
    {
        return array_unique(array_column($affectedDataLines, 'product_id'));
    }
}
