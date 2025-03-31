<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Product\Grid\AllItems;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection as DefaultCollection;
use M2E\Kaufland\Model\ResourceModel\Account as AccountResource;
use M2E\Kaufland\Model\ResourceModel\Listing as ListingResource;
use M2E\Kaufland\Model\ResourceModel\Product as ProductResource;
use M2E\Kaufland\Model\ResourceModel\Tag\ListingProduct\Relation as TagProductRelationResource;
use M2E\Kaufland\Model\ResourceModel\Tag as TagResource;
use M2E\Kaufland\Model\ResourceModel\Magento\Product\Collection as WrappedCollection;
use M2E\Kaufland\Model\Product\Ui\RuntimeStorage;
use M2E\Kaufland\Model\ResourceModel\Magento\Product\CollectionFactory;

class Collection extends DefaultCollection implements SearchResultInterface
{
    use \M2E\Kaufland\Model\ResourceModel\Product\Grid\SearchResultTrait;

    public const PRIMARY_COLUMN = 'product_id';
    public const FILTER_BY_ERROR_CODE_FILED_NAME = 'error_code'; // see ui xml

    private bool $isAlreadyFilteredByErrorCode = false;

    private ProductResource $listingProductResource;

    private ListingResource $listingResource;

    private AccountResource $accountResource;

    private WrappedCollection $wrappedCollection;

    private RuntimeStorage $productUiRuntimeStorage;
    private TagProductRelationResource $tagProductRelationResource;

    private TagResource $tagResource;
    private bool $isGetAllItemsFromFilter = false;

    private AggregationInterface $aggregations;

    public function __construct(
        ProductResource $listingProductResource,
        ListingResource $listingResource,
        AccountResource $accountResource,
        TagProductRelationResource $tagProductRelationResource,
        TagResource $tagResource,
        RuntimeStorage $productUiRuntimeStorage,
        CollectionFactory $magentoProductCollectionFactory,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
    ) {
        parent::__construct($entityFactory);
        $this->listingProductResource = $listingProductResource;
        $this->listingResource = $listingResource;
        $this->accountResource = $accountResource;
        $this->productUiRuntimeStorage = $productUiRuntimeStorage;
        $this->wrappedCollection = $magentoProductCollectionFactory->create();
        $this->tagProductRelationResource = $tagProductRelationResource;
        $this->tagResource = $tagResource;
        $this->prepareCollection();
    }

    private function prepareCollection(): void
    {
        $this->wrappedCollection->setItemObjectClass(Entity::class);

        $this->wrappedCollection->setListingProductModeOn();

        $this->wrappedCollection->getSelect()->distinct();

        $this->wrappedCollection->addAttributeToSelect('sku');
        $this->wrappedCollection->addAttributeToSelect('name');

        $this->wrappedCollection->joinTable(
            ['lp' => $this->listingProductResource->getMainTable()],
            sprintf('%s = entity_id', ProductResource::COLUMN_MAGENTO_PRODUCT_ID),
            [
                self::PRIMARY_COLUMN => ProductResource::COLUMN_ID,
                'product_' . ProductResource::COLUMN_STATUS => ProductResource::COLUMN_STATUS,
                'product_' . ProductResource::COLUMN_LISTING_ID => ProductResource::COLUMN_LISTING_ID,
                'product_' . ProductResource::COLUMN_ONLINE_QTY => ProductResource::COLUMN_ONLINE_QTY,
                'product_' . ProductResource::COLUMN_ONLINE_TITLE => ProductResource::COLUMN_ONLINE_TITLE,
                'product_' . ProductResource::COLUMN_ONLINE_PRICE => ProductResource::COLUMN_ONLINE_PRICE,
                'product_' . ProductResource::COLUMN_OFFER_ID => ProductResource::COLUMN_OFFER_ID,
                'product_' . ProductResource::COLUMN_TEMPLATE_CATEGORY_ID => ProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
                'product_' . ProductResource::COLUMN_KAUFLAND_PRODUCT_ID => ProductResource::COLUMN_KAUFLAND_PRODUCT_ID,
                'product_' . ProductResource::COLUMN_IS_INCOMPLETE => ProductResource::COLUMN_IS_INCOMPLETE,
            ],
        );

        $this->wrappedCollection->joinTable(
            ['listing' => $this->listingResource->getMainTable()],
            sprintf('%s = product_%s', ListingResource::COLUMN_ID, ProductResource::COLUMN_LISTING_ID),
            [
                'listing_' . ListingResource::COLUMN_STORE_ID => ListingResource::COLUMN_STORE_ID,
                'listing_' . ListingResource::COLUMN_STOREFRONT_ID => ListingResource::COLUMN_STOREFRONT_ID,
                'listing_' . ListingResource::COLUMN_ACCOUNT_ID => ListingResource::COLUMN_ACCOUNT_ID,
                'listing_' . ListingResource::COLUMN_TITLE => ListingResource::COLUMN_TITLE,
                'listing_' . ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID => ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID,
                'listing_' . ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID => ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID,
                'listing_' . ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID => ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID,
            ],
        );

        $this->wrappedCollection->joinTable(
            ['account' => $this->accountResource->getMainTable()],
            sprintf('%s = listing_%s', AccountResource::COLUMN_ID, ListingResource::COLUMN_ACCOUNT_ID),
            [
                'account_' . AccountResource::COLUMN_TITLE => AccountResource::COLUMN_TITLE,
            ],
        );
    }

    public function getItems()
    {
        $items = $this->wrappedCollection->getItems();
        $productIds = [];
        foreach ($items as $item) {
            $productIds[] = (int)$item['product_id'];
        }

        if (!$this->isGetAllItemsFromFilter) {
            $this->productUiRuntimeStorage->loadByIds(array_unique($productIds));
        }

        return $items;
    }

    public function getProducts(): array
    {
        return $this->productUiRuntimeStorage->getAll();
    }

    public function getSelect()
    {
        return $this->wrappedCollection->getSelect();
    }

    // ----------------------------------------

    public function addFieldToFilter($field, $condition)
    {
        if ($field === self::FILTER_BY_ERROR_CODE_FILED_NAME) {
            $this->addFilterByTag($condition);

            return $this;
        }

        if ($field === 'product_status') {
            $this->buildFilterByStatus($condition);

            return $this;
        }

        $this->wrappedCollection->addFieldToFilter($field, $condition);

        return $this;
    }

    private function addFilterByTag($condition): void
    {
        $errorCode = null;
        if (isset($condition['eq'])) {
            $errorCode = [$condition['eq']];
        } elseif (isset($condition['in'])) {
            $errorCode = $condition['in'];
        }

        if ($errorCode === null) {
            return;
        }

        if (!$this->isAlreadyFilteredByErrorCode) {
            $this->wrappedCollection->joinTable(
                ['tag_product_relation' => $this->tagProductRelationResource->getMainTable()],
                sprintf(
                    '%s = %s',
                    TagProductRelationResource::COLUMN_LISTING_PRODUCT_ID,
                    self::PRIMARY_COLUMN,
                ),
                [
                    'tag_product_relation_id' => TagProductRelationResource::COLUMN_LISTING_PRODUCT_ID,
                    'tag_product_relation_tag_id' => TagProductRelationResource::COLUMN_TAG_ID,
                ],
            );

            $this->wrappedCollection->joinTable(
                ['tag' => $this->tagResource->getMainTable()],
                sprintf(
                    '%s = tag_product_relation_tag_id',
                    TagResource::COLUMN_ID,
                ),
                ['tag_id' => TagResource::COLUMN_ID],
            );

            $this->isAlreadyFilteredByErrorCode = true;
        }

        $this->wrappedCollection->getSelect()
                                ->where(sprintf('tag.%s in (?)', TagResource::COLUMN_ERROR_CODE), $errorCode);
    }

    private function buildFilterByStatus($condition): void
    {
        if (isset($condition['eq']) && $condition['eq'] === \M2E\Kaufland\Ui\Select\ProductStatus::STATUS_INCOMPLETE) {
            $this->wrappedCollection->addFieldToFilter('product_' . ProductResource::COLUMN_IS_INCOMPLETE, 1);
        } else {
            $fieldStatus = 'lp.' . ProductResource::COLUMN_STATUS;
            $fieldIncomplete = 'lp.' . ProductResource::COLUMN_IS_INCOMPLETE;

            $this->wrappedCollection->getSelect()
                ->where($fieldStatus . ' = ? AND ' . $fieldIncomplete . ' = 0', $condition['eq']);
        }
    }

    public function setPageSize($size)
    {
        if ($size === false) {
            $this->isGetAllItemsFromFilter = true;
        }

        $this->wrappedCollection->setPageSize($size);

        return $this;
    }

    public function setCurPage($page)
    {
        $this->wrappedCollection->setCurPage($page);

        return $this;
    }

    public function setOrder($field, $direction = \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
    {
        if ($field === 'column_title') {
            $field = 'name';
        }

        $this->wrappedCollection->setOrder($field, $direction);

        return $this;
    }

    public function getTotalCount(): int
    {
        return $this->wrappedCollection->getSize();
    }
}
