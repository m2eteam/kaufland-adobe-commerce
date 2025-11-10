<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Actions\Mode\Category;

class Repository
{
    /** @var \M2E\Kaufland\Model\Listing[] */
    private array $cacheListing = [];
    /** @var \M2E\Kaufland\Model\Listing\Auto\Category\Group[][] */
    private array $cacheAutoCategoryGroups = [];
    /** @var int[] */
    private array $cacheStoreWebsiteId = [];

    private GroupSetFactory $groupSetFactory;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\CollectionFactory $autoCategoryCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group\CollectionFactory $autoCategoryGroupCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group $autoCategoryGroupResource;
    private \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;
    private \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        GroupSetFactory $groupSetFactory,
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\CollectionFactory $autoCategoryCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group $autoCategoryGroupResource,
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group\CollectionFactory $autoCategoryGroupCollFactory,
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->groupSetFactory = $groupSetFactory;
        $this->autoCategoryCollectionFactory = $autoCategoryCollectionFactory;
        $this->autoCategoryGroupCollectionFactory = $autoCategoryGroupCollFactory;
        $this->autoCategoryGroupResource = $autoCategoryGroupResource;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->listingRepository = $listingRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param int[] $categoryIds
     * @param \Magento\Catalog\Model\Product|null $magentoProduct
     *
     * @return GroupSet
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGroupSet(
        array $categoryIds,
        ?\Magento\Catalog\Model\Product $magentoProduct = null
    ): GroupSet {
        $autoCategoryCollection = $this->autoCategoryCollectionFactory->create();
        $autoCategoryCollection->selectCategoryId();
        $autoCategoryCollection->selectGroupId();

        $autoCategoryCollection->join(
            ['acg' => $this->autoCategoryGroupResource->getMainTable()],
            'group_id=acg.id',
            ['listing_id']
        );

        $autoCategorySubCollection = $this->autoCategoryCollectionFactory->create();
        $autoCategorySubCollection->selectGroupId();
        $autoCategorySubCollection->whereCategoryIdIn($categoryIds);

        $autoCategoryCollection->getSelect()->where(
            'main_table.group_id IN (?)',
            $autoCategorySubCollection->getSelect()
        );

        if ($magentoProduct) {
            $listingProductCollection = $this->listingProductCollectionFactory->create();
            $listingProductCollection->addFieldToSelect(
                \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_LISTING_ID
            );
            $listingProductCollection->addFieldToFilter(
                \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_MAGENTO_PRODUCT_ID,
                ['eq' => $magentoProduct->getId()]
            );

            $autoCategoryCollection->getSelect()->where(
                'acg.listing_id IN (?)',
                $listingProductCollection->getSelect()
            );
        }

        return $this->makeFilledGroupSet($autoCategoryCollection->toArray()['items']);
    }

    /**
     * @param array $items
     *
     * @return GroupSet
     */
    private function makeFilledGroupSet(array $items): GroupSet
    {
        $groups = [];
        foreach ($items as $item) {
            $groups[$item['listing_id']]['category_ids'][] = $item['category_id'];
            $groups[$item['listing_id']]['group_ids'][] = $item['group_id'];
        }

        $groupSet = $this->groupSetFactory->create();
        foreach ($groups as $listingId => $val) {
            $groupSet->fillGroupData($listingId, $val['category_ids'], $val['group_ids']);
        }

        return $groupSet;
    }

    /**
     * @param int $listingId
     *
     * @return int
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreWebsiteIdByListingId(int $listingId): int
    {
        if (isset($this->cacheStoreWebsiteId[$listingId])) {
            return $this->cacheStoreWebsiteId[$listingId];
        }

        $listing = $this->getLoadedListing($listingId);

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore($listing->getStoreId());
        $id = (int)$store->getWebsite()->getId();

        return $this->cacheStoreWebsiteId[$listingId] = $id;
    }

    /**
     * @param int $listingId
     *
     * @return \M2E\Kaufland\Model\Listing
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getLoadedListing(int $listingId): \M2E\Kaufland\Model\Listing
    {
        if (isset($this->cacheListing[$listingId])) {
            return $this->cacheListing[$listingId];
        }

        return $this->cacheListing[$listingId] = $this->listingRepository->get($listingId);
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Auto\Actions\Mode\Category\Group $group
     *
     * @return \M2E\Kaufland\Model\Listing\Auto\Category\Group[]
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getAutoCategoryGroups(Group $group): array
    {
        $autoCategoryGroupIds = array_unique($group->getAutoCategoryGroupIds());
        if (count($autoCategoryGroupIds) === 0) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Not allowed value');
        }

        sort($autoCategoryGroupIds);
        $cacheKey = implode(',', $autoCategoryGroupIds);
        if (isset($this->cacheAutoCategoryGroups[$cacheKey])) {
            return $this->cacheAutoCategoryGroups[$cacheKey];
        }

        $collection = $this->autoCategoryGroupCollectionFactory->create();
        $collection->getSelect()->where('id IN (?)', $autoCategoryGroupIds);

        $collection->whereAddingOrDeletingModeEnabled();
        $items = $collection->getItems();

        return $this->cacheAutoCategoryGroups[$cacheKey] = $items;
    }
}
