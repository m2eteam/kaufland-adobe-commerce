<?php

namespace M2E\Kaufland\Model\Product;

use M2E\Kaufland\Model\ResourceModel\Listing as ListingResource;
use M2E\Kaufland\Model\ResourceModel\Product as ProductResource;
use M2E\Kaufland\Model\ResourceModel\ExternalChange as ExternalChangeResource;
use M2E\Kaufland\Model\ResourceModel\Product as ListingProductResource;
use Magento\Ui\Component\MassAction\Filter as MassActionFilter;

class Repository
{
    private ListingProductResource $listingProductResource;
    private ProductResource\CollectionFactory $listingProductCollectionFactory;
    private \M2E\Kaufland\Model\Listing\Wizard\ProductCollectionFactory $wizardProductCollectionFactory;
    private \M2E\Kaufland\Model\ProductFactory $listingProductFactory;
    private \M2E\Kaufland\Helper\Data\Cache\Runtime $runtimeCache;
    private \M2E\Kaufland\Model\ResourceModel\Listing $listingResource;
    private \M2E\Kaufland\Model\ResourceModel\ExternalChange $externalChangeResource;
    private \M2E\Kaufland\Helper\Module\Database\Structure $dbStructureHelper;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Listing $listingResource,
        \M2E\Kaufland\Helper\Data\Cache\Runtime $runtimeCache,
        ListingProductResource $listingProductResource,
        ProductResource\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\ExternalChange $externalChangeResource,
        \M2E\Kaufland\Model\ProductFactory $listingProductFactory,
        \M2E\Kaufland\Model\Listing\Wizard\ProductCollectionFactory $wizardProductCollectionFactory,
        \M2E\Kaufland\Helper\Module\Database\Structure $dbStructureHelper
    ) {
        $this->listingProductResource = $listingProductResource;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->listingProductFactory = $listingProductFactory;
        $this->runtimeCache = $runtimeCache;
        $this->listingResource = $listingResource;
        $this->externalChangeResource = $externalChangeResource;
        $this->wizardProductCollectionFactory = $wizardProductCollectionFactory;
        $this->dbStructureHelper = $dbStructureHelper;
    }

    public function create(\M2E\Kaufland\Model\Product $product): void
    {
        $this->listingProductResource->save($product);
    }

    public function save(
        \M2E\Kaufland\Model\Product $product
    ): void {
        $this->listingProductResource->save($product);
    }

    public function find(int $id): ?\M2E\Kaufland\Model\Product
    {
        $listingProduct = $this->listingProductFactory->create();
        $this->listingProductResource->load($listingProduct, $id);

        if ($listingProduct->isObjectNew()) {
            return null;
        }

        return $listingProduct;
    }

    public function get(int $id): \M2E\Kaufland\Model\Product
    {
        $listingProduct = $this->find($id);
        if ($listingProduct === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Listing product not found.');
        }

        return $listingProduct;
    }

    /**
     * @param $magentoProductId
     * @param array $listingFilters
     * @param array $listingProductFilters
     *
     * @return \M2E\Kaufland\Model\Product[]
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function getItemsByMagentoProductId(
        int $magentoProductId,
        array $listingFilters = [],
        array $listingProductFilters = []
    ): array {
        $filters = [$listingFilters, $listingProductFilters];
        $cacheKey = __METHOD__ . $magentoProductId . sha1(\M2E\Core\Helper\Json::encode($filters));
        $cacheValue = $this->runtimeCache->getValue($cacheKey);

        if ($cacheValue !== null) {
            return $cacheValue;
        }

        $connection = $this->listingProductCollectionFactory->create()->getConnection();

        $simpleProductsSelect = $connection
            ->select()
            ->from(
                ['lp' => $this->listingProductResource->getMainTable()],
                ['id', 'option_id' => new \Zend_Db_Expr('NULL')],
            )
            ->where(
                sprintf(
                    '`%s` = ?',
                    \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_MAGENTO_PRODUCT_ID,
                ),
                $magentoProductId,
            );

        if (!empty($listingProductFilters)) {
            foreach ($listingProductFilters as $column => $value) {
                if (is_array($value)) {
                    $simpleProductsSelect->where(
                        sprintf('`%s` IN(?)', $column),
                        $value,
                    );
                } else {
                    $simpleProductsSelect->where(
                        sprintf('`%s` = ?', $column),
                        $value,
                    );
                }
            }
        }

        if (!empty($listingFilters)) {
            $simpleProductsSelect->join(
                ['l' => $this->listingResource->getMainTable()],
                sprintf(
                    '`l`.`%s` = `lp`.`%s`',
                    \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_ID,
                    \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_LISTING_ID,
                ),
                [],
            );

            foreach ($listingFilters as $column => $value) {
                if (is_array($value)) {
                    $simpleProductsSelect->where(
                        sprintf('`l`.`%s` IN(?)', $column),
                        $value,
                    );
                } else {
                    $simpleProductsSelect->where(
                        sprintf('`l`.`%s` = ?', $column),
                        $value,
                    );
                }
            }
        }

        $connection = $this->listingProductResource->getConnection();

        $unionSelect = $connection->select()->union([
            $simpleProductsSelect,
        ]);

        $result = [];
        $foundOptionsIds = [];

        foreach ($unionSelect->query()->fetchAll() as $item) {
            $tempListingProductId = $item['id'];

            if (!empty($item['option_id'])) {
                $foundOptionsIds[$tempListingProductId][] = $item['option_id'];
            }

            if (!empty($result[$tempListingProductId])) {
                continue;
            }

            $result[$tempListingProductId] = $this->get((int)$tempListingProductId);
        }

        foreach ($foundOptionsIds as $listingProductId => $optionsIds) {
            /** @var non-empty-list<mixed> $optionsIds */
            if (empty($result[$listingProductId]) || empty($optionsIds)) {
                continue;
            }
            $result[$listingProductId]->setData('found_options_ids', $optionsIds);
        }

        $this->runtimeCache->setValue($cacheKey, $result);

        return array_values($result);
    }

    public function delete(\M2E\Kaufland\Model\Product $listingProduct): void
    {
        $this->listingProductResource->delete($listingProduct);
    }

    /**
     * @return \M2E\Kaufland\Model\Product[]
     */
    public function findByListing(\M2E\Kaufland\Model\Listing $listing): array
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            ProductResource::COLUMN_LISTING_ID,
            ['eq' => $listing->getId()],
        );

        foreach ($collection->getItems() as $product) {
            $product->initListing($listing);
        }

        return array_values($collection->getItems());
    }

    public function findByListingAndMagentoProductId(
        \M2E\Kaufland\Model\Listing $listing,
        int $magentoProductId
    ): ?\M2E\Kaufland\Model\Product {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            ProductResource::COLUMN_LISTING_ID,
            ['eq' => $listing->getId()],
        );
        $collection->addFieldToFilter(
            ProductResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['eq' => $magentoProductId],
        );

        $product = $collection->getFirstItem();
        if ($product->isObjectNew()) {
            return null;
        }

        $product->initListing($listing);

        return $product;
    }

    /**
     * @return \M2E\Kaufland\Model\Product[]
     */
    public function findByIds(array $listingProductsIds): array
    {
        if (empty($listingProductsIds)) {
            return [];
        }

        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            ProductResource::COLUMN_ID,
            ['in' => $listingProductsIds],
        );

        return array_values($collection->getItems());
    }

    /**
     * @return \M2E\Kaufland\Model\Product[]
     */
    public function findByMagentoProductId(int $magentoProductId): array
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            ProductResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['eq' => $magentoProductId],
        );

        return array_values($collection->getItems());
    }

    /**
     * @param string $sku
     *
     * @return \M2E\Kaufland\Model\Product[]
     */
    public function findProductsByMagentoSku(
        string $sku
    ): array {
        $collection = $this->listingProductCollectionFactory->create();
        $entityTableName = $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity');

        $collection->getSelect()
                   ->join(
                       ['cpe' => $entityTableName],
                       sprintf(
                           'cpe.entity_id = `main_table`.%s',
                           ProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                       ),
                       [],
                   );
        $collection->addFieldToFilter(
            'cpe.sku',
            ['like' => '%' . $sku . '%'],
        );

        return $collection->getItems();
    }

    /**
     * @return \M2E\Kaufland\Model\Product[]
     */
    public function findStatusListedByListing(\M2E\Kaufland\Model\Listing $listing): array
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            ProductResource::COLUMN_LISTING_ID,
            ['eq' => $listing->getId()],
        );
        $collection->addFieldToFilter(
            ProductResource::COLUMN_STATUS,
            ['eq' => \M2E\Kaufland\Model\Product::STATUS_LISTED],
        );

        return array_values($collection->getItems());
    }

    /**
     * @param string $kauflandProductId
     * @param string $offerId
     * @param int $storefront
     *
     * @return \M2E\Kaufland\Model\Product|null
     */
    public function findByKauflandProductIdOfferIdAndStorefrontId(
        string $kauflandProductId,
        string $offerId,
        int $storefrontId
    ): ?\M2E\Kaufland\Model\Product {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(ProductResource::COLUMN_KAUFLAND_PRODUCT_ID, $kauflandProductId);
        $collection->addFieldToFilter(ProductResource::COLUMN_OFFER_ID, $offerId);
        $collection->addFieldToFilter(ProductResource::COLUMN_STOREFRONT_ID, $storefrontId);

        $product = $collection->getFirstItem();

        if ($product->isObjectNew()) {
            return null;
        }

        return $product;
    }

    // ----------------------------------------

    /**
     * @param int $listingId
     *
     * @return int[]
     */
    public function findMagentoProductIdsByListingId(int $listingId): array
    {
        $collection = $this->listingProductCollectionFactory->create();

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);

        $collection
            ->addFieldToSelect(ProductResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addFieldToSelect(ProductResource::COLUMN_ID) // for load collection
            ->addFieldToFilter(ProductResource::COLUMN_LISTING_ID, $listingId);

        $result = [];
        foreach ($collection->getItems() as $product) {
            $result[] = $product->getMagentoProductId();
        }

        return $result;
    }

    public function getCountListedProductsForListing(\M2E\Kaufland\Model\Listing $listing): int
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection
            ->addFieldToFilter(ListingProductResource::COLUMN_LISTING_ID, $listing->getId())
            ->addFieldToFilter(ListingProductResource::COLUMN_STATUS, \M2E\Kaufland\Model\Product::STATUS_LISTED);

        return (int)$collection->getSize();
    }

    public function getStatisticForSearchChannelId(int $listingId, array $forProductIds): array
    {
        $collection = $this->wizardProductCollectionFactory->create();

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns(
            [
                'product_id_search_status' => \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product::COLUMN_PRODUCT_ID_SEARCH_STATUS,
                'count' => new \Zend_Db_Expr('COUNT(*)'),
            ],
        );
        $collection->getSelect()->group(
            \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product::COLUMN_PRODUCT_ID_SEARCH_STATUS,
        );

        $collection
            ->addFieldToFilter(
                \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product::COLUMN_MAGENTO_PRODUCT_ID,
                ['in' => $forProductIds],
            )
            ->addFieldToFilter(
                \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product::COLUMN_WIZARD_ID,
                $listingId,
            );

        $select = (string)$collection->getSelect();

        $data = $this->listingProductResource->getConnection()
                                             ->fetchPairs($select);
        $result = [];
        foreach ($data as $status => $count) {
            $result[$status] = (int)$count;
        }

        return $result;
    }

    public function findByKauflandOfferIds(
        array $kauflandOfferIds,
        int $accountId,
        int $storefrontId,
        ?int $listingId = null
    ): array {
        if (empty($kauflandOfferIds)) {
            return [];
        }

        $collection = $this->listingProductCollectionFactory->create();
        $collection
            ->join(
                ['l' => $this->listingResource->getMainTable()],
                sprintf(
                    '`l`.%s = `main_table`.%s',
                    ListingResource::COLUMN_ID,
                    ProductResource::COLUMN_LISTING_ID,
                ),
                [],
            )
            ->addFieldToFilter(
                sprintf('main_table.%s', ProductResource::COLUMN_OFFER_ID),
                ['in' => $kauflandOfferIds],
            )
            ->addFieldToFilter(sprintf('l.%s', ListingResource::COLUMN_ACCOUNT_ID), $accountId)
            ->addFieldToFilter(sprintf('l.%s', ListingResource::COLUMN_STOREFRONT_ID), $storefrontId)
            ->addFieldToFilter(
                sprintf('main_table.%s', ProductResource::COLUMN_STATUS),
                ['neq' => \M2E\Kaufland\Model\Product::STATUS_NOT_LISTED]
            );

        if ($listingId !== null) {
            $collection->addFieldToFilter(sprintf('l.%s', ListingResource::COLUMN_ID), $listingId);
        }

        return array_values($collection->getItems());
    }

    /**
     * @param int $accountId
     * @param int $storefrontId
     * @param \DateTime $inventorySyncProcessingStartDate
     *
     * @return \M2E\Kaufland\Model\Product[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findRemovedFromChannel(
        int $accountId,
        int $storefrontId,
        \DateTime $inventorySyncProcessingStartDate
    ): array {
        $joinConditions = [
            sprintf(
                '`ec`.%s = `main_table`.%s',
                ExternalChangeResource::COLUMN_UNIT_ID,
                ProductResource::COLUMN_UNIT_ID,
            ),
            sprintf(
                '`ec`.%s = `main_table`.%s',
                ExternalChangeResource::COLUMN_STOREFRONT_ID,
                ProductResource::COLUMN_STOREFRONT_ID,
            ),
            sprintf(
                '`ec`.%s = `l`.%s',
                ExternalChangeResource::COLUMN_ACCOUNT_ID,
                ListingResource::COLUMN_ACCOUNT_ID,
            )
        ];

        $collection = $this->listingProductCollectionFactory->create();

        $collection->join(
            ['l' => $this->listingResource->getMainTable()],
            sprintf(
                '`l`.%s = `main_table`.%s',
                ListingResource::COLUMN_ID,
                ProductResource::COLUMN_LISTING_ID,
            ),
            [],
        );
        $collection->joinLeft(
            [
                'ec' => $this->externalChangeResource->getMainTable(),
            ],
            implode(' AND ', $joinConditions),
            [],
        );

        $collection
            ->addFieldToFilter(
                sprintf('main_table.%s', ProductResource::COLUMN_STATUS),
                ['neq' => \M2E\Kaufland\Model\Product::STATUS_NOT_LISTED],
            )
            ->addFieldToFilter(sprintf('main_table.%s', ProductResource::COLUMN_STOREFRONT_ID), $storefrontId)
            ->addFieldToFilter(sprintf('l.%s', ListingResource::COLUMN_ACCOUNT_ID), $accountId)
            ->addFieldToFilter('ec.id', ['null' => true]);
        /**
         * Excluding listing products created after current inventory sync processing start date
         */
        $collection->getSelect()->where(
            sprintf('main_table.%s ', ProductResource::COLUMN_ID)
            . 'NOT IN (?)',
            $this->getExcludedByDateSubSelect($inventorySyncProcessingStartDate)
        );

        return array_values($collection->getItems());
    }

    /**
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     *
     * @return \M2E\Kaufland\Model\Product[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function massActionSelectedProducts(MassActionFilter $filter): array
    {
        $collection = $this->listingProductCollectionFactory->create();
        $filter->getCollection($collection);

        return array_values($collection->getItems());
    }

    private function getExcludedByDateSubSelect(\DateTime $inventorySyncProcessingStartDate)
    {
        return new \Zend_Db_Expr(
            sprintf(
                'SELECT `%s` FROM `%s` WHERE `%s`=%s AND `%s` > "%s"',
                ProductResource::COLUMN_ID,
                $this->listingProductResource->getMainTable(),
                ProductResource::COLUMN_STATUS,
                \M2E\Kaufland\Model\Product::STATUS_LISTED,
                ProductResource::COLUMN_STATUS_CHANGE_DATE,
                $inventorySyncProcessingStartDate->format('Y-m-d H:i:s'),
            )
        );
    }

    public function updateLastBlockingErrorDate(array $listingProductIds, \DateTime $dateTime): void
    {
        if (empty($listingProductIds)) {
            return;
        }

        $this->listingProductResource->getConnection()->update(
            $this->listingProductResource->getMainTable(),
            [ListingProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE => $dateTime->format('Y-m-d H:i:s')],
            ['id IN (?)' => $listingProductIds]
        );
    }

    public function findIdsByListingId(int $listingId): array
    {
        if (empty($listingId)) {
            return [];
        }

        $select = $this->listingProductResource->getConnection()
            ->select()
            ->from($this->listingProductResource->getMainTable(), 'id')
            ->where('listing_id = ?', $listingId);

        return array_column($select->query()->fetchAll(), 'id');
    }

    public function cleanOfferIdForNotListed(int $listingId): void
    {
        $this->listingProductResource->getConnection()->update(
            $this->listingProductResource->getMainTable(),
            [ListingProductResource::COLUMN_OFFER_ID => null],
            [
                ListingProductResource::COLUMN_LISTING_ID . ' = ?' => $listingId,
                ListingProductResource::COLUMN_STATUS . ' = ?' => \M2E\Kaufland\Model\Product::STATUS_NOT_LISTED
            ]
        );
    }

    public function createCollectionByListingDescriptionPolicy(int $policyId): ProductResource\Collection
    {
        return $this->getProductCollectionByTemplateId(ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID, $policyId);
    }

    public function createCollectionByListingSellingPolicy(int $policyId): ProductResource\Collection
    {
        return $this->getProductCollectionByTemplateId(ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID, $policyId);
    }

    public function createCollectionByListingSyncPolicy(int $policyId): ProductResource\Collection
    {
        return $this->getProductCollectionByTemplateId(ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID, $policyId);
    }

    public function createCollectionByListingShippingPolicy(int $policyId): ProductResource\Collection
    {
        return $this->getProductCollectionByTemplateId(ListingResource::COLUMN_TEMPLATE_SHIPPING_ID, $policyId);
    }

    /**
     * @param string $columnTemplateIdName
     * @param int $columnTemplateId
     *
     * @return \M2E\Kaufland\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getProductCollectionByTemplateId(string $columnTemplateIdName, int $columnTemplateId): ProductResource\Collection
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->joinInner(
            ['listing' => $this->listingResource->getMainTable()],
            'listing_id = listing.id',
            []
        );

        $collection->getSelect()->where(
            sprintf('`listing`.`%s` = ?', $columnTemplateIdName),
            $columnTemplateId
        );

        return $collection;
    }

    // ----------------------------------------

    public function createCollectionByCategoryTemplate(int $categoryId): ProductResource\Collection
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_TEMPLATE_CATEGORY_ID,
            ['eq' => $categoryId]
        );

        return $collection;
    }

    public function getIds(int $fromId, int $limit): array
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter('id', ['gt' => $fromId]);
        $collection->getSelect()->order(['id ASC']);
        $collection->getSelect()->limit($limit);

        return array_map('intval', $collection->getColumnValues('id'));
    }
}
