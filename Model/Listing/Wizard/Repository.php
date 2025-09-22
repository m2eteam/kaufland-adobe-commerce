<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Wizard;

use M2E\Kaufland\Model\ResourceModel\Listing\Wizard as WizardResource;
use M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product as WizardProductResource;

class Repository
{
    private WizardResource $wizardResource;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Step $stepResource;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Step\CollectionFactory $stepCollectionFactory;
    private \M2E\Kaufland\Model\Listing\WizardFactory $wizardFactory;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\CollectionFactory $wizardCollectionFactory;
    private WizardProductResource $wizardProductResource;
    private \M2E\Kaufland\Model\Listing\Wizard\ProductCollectionFactory $productCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product\CollectionFactory $wizardProductCollectionFactory;
    /**
     * @var WizardProductResource
     */
    private WizardProductResource $productResource;
    private \M2E\Kaufland\Helper\Module\Database\Tables $tablesHelper;

    public function __construct(
        \M2E\Kaufland\Model\Listing\WizardFactory $wizardFactory,
        WizardResource $wizardResource,
        \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\CollectionFactory $wizardCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Step $stepResource,
        \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Step\CollectionFactory $stepCollectionFactory,
        WizardProductResource $wizardProductResource,
        \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product\CollectionFactory $wizardProductCollectionFactory,
        \M2E\Kaufland\Model\Listing\Wizard\ProductCollectionFactory $productCollectionFactory,
        WizardProductResource $productResource,
        \M2E\Kaufland\Helper\Module\Database\Tables $tablesHelper
    ) {
        $this->wizardResource = $wizardResource;
        $this->stepResource = $stepResource;
        $this->stepCollectionFactory = $stepCollectionFactory;
        $this->wizardFactory = $wizardFactory;
        $this->wizardCollectionFactory = $wizardCollectionFactory;
        $this->wizardProductResource = $wizardProductResource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->wizardProductCollectionFactory = $wizardProductCollectionFactory;
        $this->productResource = $productResource;
        $this->tablesHelper = $tablesHelper;
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     *
     * @return void
     */
    public function create(\M2E\Kaufland\Model\Listing\Wizard $wizard): void
    {
        $this->wizardResource->save($wizard);
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard\Step[] $steps
     *
     * @return void
     */
    public function createSteps(array $steps): void
    {
        foreach ($steps as $step) {
            $this->stepResource->save($step);
        }
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     *
     * @return void
     */
    public function save(\M2E\Kaufland\Model\Listing\Wizard $wizard): void
    {
        $this->wizardResource->save($wizard);
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard\Step $step
     *
     * @return void
     */
    public function saveStep(Step $step): void
    {
        $this->stepResource->save($step);
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard\Product $product
     *
     * @return void
     */
    public function saveProduct(Product $product): void
    {
        $this->wizardProductResource->save($product);
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard\Product $product
     *
     * @return void
     */
    public function removeProduct(Product $product): void
    {
        $this->wizardProductResource->delete($product);
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     *
     * @return void
     */
    public function remove(\M2E\Kaufland\Model\Listing\Wizard $wizard): void
    {
        foreach ($wizard->getSteps() as $step) {
            $this->stepResource->delete($step);
        }
        $this->removeAllProducts($wizard);

        $this->wizardResource->delete($wizard);
    }

    // ----------------------------------------

    /**
     * @param int $id
     *
     * @return \M2E\Kaufland\Model\Listing\Wizard
     */
    public function get(int $id): \M2E\Kaufland\Model\Listing\Wizard
    {
        $wizard = $this->find($id);
        if ($wizard === null) {
            throw new \M2E\Kaufland\Model\Listing\Wizard\Exception\NotFoundException('Wizard not found.');
        }

        return $wizard;
    }

    /**
     * @param int $id
     *
     * @return \M2E\Kaufland\Model\Listing\Wizard|null
     */
    public function find(int $id): ?\M2E\Kaufland\Model\Listing\Wizard
    {
        $wizard = $this->wizardFactory->create();
        $this->wizardResource->load($wizard, $id);

        if ($wizard->isObjectNew()) {
            return null;
        }

        $this->loadSteps($wizard);

        return $wizard;
    }

    /**
     * @param int $id
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     *
     * @return \M2E\Kaufland\Model\Listing\Wizard\Product|null
     */
    public function findProductById(
        int $id,
        \M2E\Kaufland\Model\Listing\Wizard $wizard
    ): ?\M2E\Kaufland\Model\Listing\Wizard\Product {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->addFieldToFilter(
                WizardProductResource::COLUMN_WIZARD_ID,
                $wizard->getId(),
            )
            ->addFieldToFilter(WizardProductResource::COLUMN_ID, ['eq' => $id]);

        $product = $productCollection->getFirstItem();
        if ($product->isObjectNew()) {
            return null;
        }

        $product->initWizard($wizard);

        return $product;
    }

    /**
     * @param string $type
     *
     * @return \M2E\Kaufland\Model\Listing\Wizard|null
     */
    public function findNotCompletedWizardByType(string $type): ?\M2E\Kaufland\Model\Listing\Wizard
    {
        $collection = $this->wizardCollectionFactory->create();
        $collection
            ->addFieldToFilter(\M2E\Kaufland\Model\ResourceModel\Listing\Wizard::COLUMN_IS_COMPLETED, ['eq' => 0])
            ->addFieldToFilter(\M2E\Kaufland\Model\ResourceModel\Listing\Wizard::COLUMN_TYPE, ['eq' => $type]);

        $wizard = $collection->getFirstItem();
        if ($wizard->isObjectNew()) {
            return null;
        }

        $this->loadSteps($wizard);

        return $wizard;
    }

    /**
     * @param \M2E\Kaufland\Model\Listing $listing
     * @param string $type
     *
     * @return \M2E\Kaufland\Model\Listing\Wizard|null
     */
    public function findNotCompletedByListingAndType(
        \M2E\Kaufland\Model\Listing $listing,
        string $type
    ): ?\M2E\Kaufland\Model\Listing\Wizard {
        $collection = $this->wizardCollectionFactory->create();
        $collection
            ->addFieldToFilter(
                \M2E\Kaufland\Model\ResourceModel\Listing\Wizard::COLUMN_LISTING_ID,
                ['eq' => $listing->getId()],
            )
            ->addFieldToFilter(\M2E\Kaufland\Model\ResourceModel\Listing\Wizard::COLUMN_IS_COMPLETED, ['eq' => 0])
            ->addFieldToFilter(\M2E\Kaufland\Model\ResourceModel\Listing\Wizard::COLUMN_TYPE, ['eq' => $type]);

        $wizard = $collection->getFirstItem();
        if ($wizard->isObjectNew()) {
            return null;
        }

        $this->loadSteps($wizard);
        $wizard->initListing($listing);

        return $wizard;
    }

    /**
     * @param string $type
     *
     * @return \M2E\Kaufland\Model\Listing\Wizard|null
     */
    public function findNotCompleted(): ?\M2E\Kaufland\Model\Listing\Wizard
    {
        $collection = $this->wizardCollectionFactory->create();
        $collection
            ->addFieldToFilter(\M2E\Kaufland\Model\ResourceModel\Listing\Wizard::COLUMN_IS_COMPLETED, ['eq' => 0]);

        $wizard = $collection->getFirstItem();
        if ($wizard->isObjectNew()) {
            return null;
        }

        $this->loadSteps($wizard);

        return $wizard;
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     *
     * @return \M2E\Kaufland\Model\Listing\Wizard\Step[]
     */
    public function findSteps(\M2E\Kaufland\Model\Listing\Wizard $wizard): array
    {
        $stepCollection = $this->stepCollectionFactory->create();
        $stepCollection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Step::COLUMN_WIZARD_ID,
            $wizard->getId(),
        );

        return array_values($stepCollection->getItems());
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     *
     * @return void
     */
    private function loadSteps(\M2E\Kaufland\Model\Listing\Wizard $wizard): void
    {
        $steps = $this->findSteps($wizard);
        $wizard->initSteps($steps);
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     *
     * @return \M2E\Kaufland\Model\Listing\Wizard\Product[]
     */
    public function findAllProducts(\M2E\Kaufland\Model\Listing\Wizard $wizard): array
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addFieldToFilter(
            WizardProductResource::COLUMN_WIZARD_ID,
            $wizard->getId(),
        );

        $result = [];
        foreach ($productCollection->getItems() as $product) {
            $product->initWizard($wizard);
            $result[] = $product;
        }

        return $result;
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     *
     * @return \M2E\Kaufland\Model\Listing\Wizard\Product[]
     */
    public function findNotProcessed(\M2E\Kaufland\Model\Listing\Wizard $wizard): array
    {
        $collection = $this->wizardProductCollectionFactory->create();
        $collection
            ->addFieldToFilter(WizardProductResource::COLUMN_WIZARD_ID, $wizard->getId())
            ->addFieldToFilter(WizardProductResource::COLUMN_IS_PROCESSED, 0);

        $result = [];
        foreach ($collection->getItems() as $product) {
            $product->initWizard($wizard);
            $result[] = $product;
        }

        return $result;
    }

    /**
     * @param int $id
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     *
     * @return \M2E\Kaufland\Model\Listing\Wizard\Product|null
     */
    public function findProductByMagentoId(
        int $id,
        \M2E\Kaufland\Model\Listing\Wizard $wizard
    ): ?\M2E\Kaufland\Model\Listing\Wizard\Product {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->addFieldToFilter(\M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product::COLUMN_WIZARD_ID, $wizard->getId())
            ->addFieldToFilter(\M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product::COLUMN_MAGENTO_PRODUCT_ID, ['eq' => $id]);

        $product = $productCollection->getFirstItem();
        if ($product->isObjectNew()) {
            return null;
        }

        $product->initWizard($wizard);

        return $product;
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     *
     * @return int
     */
    public function getProcessedProductsCount(\M2E\Kaufland\Model\Listing\Wizard $wizard): int
    {
        $connection = $this->wizardProductResource->getConnection();
        $tableName = $this->wizardProductResource->getMainTable();

        $select = $connection->select()
                             ->from($tableName, ['COUNT(*)'])
                             ->where(WizardProductResource::COLUMN_WIZARD_ID . ' = ?', $wizard->getId())
                             ->where(WizardProductResource::COLUMN_IS_PROCESSED . ' = ?', 1);

        return (int)$connection->fetchOne($select);
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     *
     * @return void
     */
    public function removeAllProducts(\M2E\Kaufland\Model\Listing\Wizard $wizard): void
    {
        $this->wizardProductResource->getConnection()->delete(
            $this->wizardProductResource->getMainTable(),
            [WizardProductResource::COLUMN_WIZARD_ID . ' = ?' => $wizard->getId()],
        );
    }

    /**
     * @param \DateTime $borderDate
     *
     * @return \M2E\Kaufland\Model\Listing\Wizard[]
     */
    public function findOldCompleted(\DateTime $borderDate): array
    {
        $collection = $this->wizardCollectionFactory->create();
        $collection
            ->addFieldToFilter(WizardResource::COLUMN_IS_COMPLETED, ['eq' => 1])
            ->addFieldToFilter(WizardResource::COLUMN_PROCESS_END_DATE, ['lt' => $borderDate->format('Y-m-d H:i:s')]);

        return array_values($collection->getItems());
    }

    /**
     * @param \M2E\Kaufland\Model\Listing $listing
     *
     * @return \M2E\Kaufland\Model\Listing\Wizard[]
     */
    public function findWizardsByListing(\M2E\Kaufland\Model\Listing $listing): array
    {
        $collection = $this->wizardCollectionFactory->create();
        $collection
            ->addFieldToFilter(WizardResource::COLUMN_LISTING_ID, ['eq' => $listing->getId()]);

        return array_values($collection->getItems());
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     * @param int[] $wizardProductsIds
     *
     * @return void
     */
    public function markProductsAsCompleted(
        \M2E\Kaufland\Model\Listing\Wizard $wizard,
        array $wizardProductsIds
    ): void {
        $this->wizardProductResource
            ->getConnection()
            ->update(
                $this->wizardProductResource->getMainTable(),
                [
                    WizardProductResource::COLUMN_IS_PROCESSED => 1,
                ],
                [
                    sprintf('%s = %d', WizardProductResource::COLUMN_WIZARD_ID, $wizard->getId()),
                    sprintf('%s IN (%s)', WizardProductResource::COLUMN_ID, implode(',', $wizardProductsIds)),
                ],
            );
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     * @param int $categoryDictionaryId
     *
     * @return void
     */
    public function setCategoryDictionaryIdForProducts(
        \M2E\Kaufland\Model\Listing\Wizard $wizard,
        int $categoryDictionaryId
    ): void {
        $this->productResource
            ->getConnection()
            ->update(
                $this->tablesHelper->getFullName(
                    \M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_LISTING_WIZARD_PRODUCT,
                ),
                [
                    WizardProductResource::COLUMN_CATEGORY_ID => $categoryDictionaryId,
                ],
                sprintf(
                    '%s = %d',
                    WizardProductResource::COLUMN_WIZARD_ID,
                    $wizard->getId(),
                ),
            );
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     * @param string $categoryDictionaryTitle
     *
     * @return void
     */
    public function setCategoryDictionaryTitleForProducts(
        \M2E\Kaufland\Model\Listing\Wizard $wizard,
        string $categoryDictionaryTitle
    ): void {
        $this->productResource
            ->getConnection()
            ->update(
                $this->tablesHelper->getFullName(
                    \M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_LISTING_WIZARD_PRODUCT,
                ),
                [
                    WizardProductResource::COLUMN_CATEGORY_TITLE => $categoryDictionaryTitle,
                ],
                sprintf(
                    '%s = %d',
                    WizardProductResource::COLUMN_WIZARD_ID,
                    $wizard->getId(),
                ),
            );
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     * @param int $magentoProductId
     * @param int $categoryDictionaryId
     *
     * @return void
     */
    public function setCategoryDictionaryIdForProduct(
        \M2E\Kaufland\Model\Listing\Wizard $wizard,
        int $magentoProductId,
        int $categoryDictionaryId
    ): void {
        $this->productResource
            ->getConnection()
            ->update(
                $this->tablesHelper->getFullName(
                    \M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_LISTING_WIZARD_PRODUCT,
                ),
                [
                    WizardProductResource::COLUMN_CATEGORY_ID => $categoryDictionaryId,
                ],
                [
                    sprintf(
                        '%s = %d',
                        WizardProductResource::COLUMN_WIZARD_ID,
                        $wizard->getId(),
                    ),
                    sprintf(
                        '%s = %d',
                        WizardProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                        $magentoProductId,
                    ),
                ],
            );
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     * @param int $magentoProductId
     * @param string $categoryDictionaryTitle
     *
     * @return void
     */
    public function setCategoryDictionaryTitleForProduct(
        \M2E\Kaufland\Model\Listing\Wizard $wizard,
        int $magentoProductId,
        string $categoryDictionaryTitle
    ): void {
        $this->productResource
            ->getConnection()
            ->update(
                $this->tablesHelper->getFullName(
                    \M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_LISTING_WIZARD_PRODUCT,
                ),
                [
                    WizardProductResource::COLUMN_CATEGORY_TITLE => $categoryDictionaryTitle,
                ],
                [
                    sprintf(
                        '%s = %d',
                        WizardProductResource::COLUMN_WIZARD_ID,
                        $wizard->getId(),
                    ),
                    sprintf(
                        '%s = %d',
                        WizardProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                        $magentoProductId,
                    ),
                ],
            );
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     * @param int[] $productsIds
     *
     * @return void
     */
    public function resetCategoryIdByProductId(
        \M2E\Kaufland\Model\Listing\Wizard $wizard,
        array $productsIds
    ): void {
        $this->wizardProductResource
            ->getConnection()
            ->update(
                $this->wizardProductResource->getMainTable(),
                [
                    WizardProductResource::COLUMN_CATEGORY_ID => null,
                ],
                [
                    sprintf('%s = %d', WizardProductResource::COLUMN_WIZARD_ID, $wizard->getId()),
                    sprintf('%s in (?)', WizardProductResource::COLUMN_ID) => $productsIds,
                ],
            );
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard\Product[] $wizardProducts
     *
     * @return void
     */
    public function addOrUpdateProducts(array $wizardProducts): void
    {
        if (empty($wizardProducts)) {
            return;
        }

        $tableName = $this->wizardProductResource->getMainTable();
        $connection = $this->wizardProductResource->getConnection();

        foreach (array_chunk($wizardProducts, 500) as $productsChunk) {
            $preparedData = [];
            /** @var \M2E\Kaufland\Model\Listing\Wizard\Product $product */
            foreach ($productsChunk as $product) {
                $preparedData[] = [
                    'wizard_id' => $product->getWizardId(),
                    'unmanaged_product_id' => $product->getUnmanagedProductId(),
                    'magento_product_id' => $product->getMagentoProductId(),
                    'category_id' => $product->getCategoryDictionaryId(),
                    'is_processed' => (int)$product->isProcessed(),
                ];
            }

            $connection->insertOnDuplicate($tableName, $preparedData, ['category_id', 'is_processed']);
        }
    }

    public function findProductsForSearchChannelId(\M2E\Kaufland\Model\Listing\Wizard $wizard, int $limit): array
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->addFieldToFilter(
                WizardProductResource::COLUMN_WIZARD_ID,
                ['eq' => $wizard->getId()],
            )
            ->addFieldToFilter(
                WizardProductResource::COLUMN_PRODUCT_ID_SEARCH_STATUS,
                ['eq' => \M2E\Kaufland\Model\Listing\Wizard\Product::SEARCH_STATUS_NONE],
            )
            ->setPageSize($limit);

        $result = [];
        foreach ($productCollection->getItems() as $product) {
            $product->initWizard($wizard);

            $result[] = $product;
        }

        return $result;
    }

    public function resetSearchChannelIdForAllProducts(\M2E\Kaufland\Model\Listing\Wizard $wizard): void
    {
        $this->wizardProductResource
            ->getConnection()
            ->update(
                $this->wizardProductResource->getMainTable(),
                [
                    WizardProductResource::COLUMN_PRODUCT_ID_SEARCH_STATUS => \M2E\Kaufland\Model\Listing\Wizard\Product::SEARCH_STATUS_NONE,
                    WizardProductResource::COLUMN_KAUFLAND_PRODUCT_ID => null,
                ],
                [
                    sprintf('%s = %d', WizardProductResource::COLUMN_WIZARD_ID, $wizard->getId()),
                ],
            );
    }

    public function findNotValidProductListingWizard(int $wizardId): array
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addFieldToFilter(
            WizardProductResource::COLUMN_WIZARD_ID,
            $wizardId,
        );

        $productCollection->addFieldToFilter(
            WizardProductResource::COLUMN_KAUFLAND_PRODUCT_ID,
            ['null' => true],
        );

        return array_values($productCollection->getItems());
    }

    public function getNotValidWizardProductsIds(int $wizardId): array
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->addFieldToFilter(
                WizardProductResource::COLUMN_WIZARD_ID,
                $wizardId,
            )
            ->addFieldToFilter(
                WizardProductResource::COLUMN_KAUFLAND_PRODUCT_ID,
                ['null' => true],
            );

        $result = [];
        foreach ($productCollection->getItems() as $product) {
            $result[] = $product->getId();
        }

        return $result;
    }

    public function getCountProductsWithoutKauflandId(int $wizardId): int
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->addFieldToFilter(
                WizardProductResource::COLUMN_WIZARD_ID,
                $wizardId,
            )
            ->addFieldToFilter(
                WizardProductResource::COLUMN_KAUFLAND_PRODUCT_ID,
                ['null' => true],
            );

        return (int)$productCollection->getSize();
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     *
     * @return int
     */
    public function getProductCount(\M2E\Kaufland\Model\Listing\Wizard $wizard): int
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->addFieldToFilter(
                WizardProductResource::COLUMN_WIZARD_ID,
                ['eq' => $wizard->getId()],
            );

        return (int)$productCollection->getSize();
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard $wizard
     * @param int $productLimit
     *
     * @return \M2E\Kaufland\Model\Listing\Wizard\Product[]
     */
    public function findProductsForValidateCategoryAttributes(\M2E\Kaufland\Model\Listing\Wizard $wizard, int $productLimit): array
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->addFieldToFilter(
                WizardProductResource::COLUMN_WIZARD_ID,
                ['eq' => $wizard->getId()],
            )
            ->addFieldToFilter(
                WizardProductResource::COLUMN_IS_VALID_CATEGORY_ATTRIBUTES,
                ['null' => true]
            )
            ->addFieldToFilter(
                WizardProductResource::COLUMN_CATEGORY_ID,
                ['notnull' => true]
            )
            ->setPageSize($productLimit);

        $result = [];
        foreach ($productCollection->getItems() as $product) {
            $product->initWizard($wizard);

            $result[] = $product;
        }

        return $result;
    }

    public function resetCategoryAttributesValidationData(\M2E\Kaufland\Model\Listing\Wizard $wizard): void
    {
        $this->wizardProductResource
            ->getConnection()
            ->update(
                $this->wizardProductResource->getMainTable(),
                [
                    WizardProductResource::COLUMN_IS_VALID_CATEGORY_ATTRIBUTES => null,
                    WizardProductResource::COLUMN_CATEGORY_ATTRIBUTES_ERRORS => null,
                ],
                [
                    sprintf('%s = %d', WizardProductResource::COLUMN_WIZARD_ID, $wizard->getId()),
                ],
            );
    }

    public function resetCategoryAttributesValidationDataByCategoryId(int $categoryId): void
    {
        $this->wizardProductResource
            ->getConnection()
            ->update(
                $this->wizardProductResource->getMainTable(),
                [
                    WizardProductResource::COLUMN_IS_VALID_CATEGORY_ATTRIBUTES => null,
                    WizardProductResource::COLUMN_CATEGORY_ATTRIBUTES_ERRORS => null,
                ],
                [
                    sprintf('%s = %d', WizardProductResource::COLUMN_CATEGORY_ID, $categoryId),
                ],
            );
    }
}
