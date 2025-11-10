<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Actions;

class WebsiteMode
{
    private \Magento\Catalog\Model\Product $magentoProduct;
    private \M2E\Kaufland\Model\Listing\Auto\Actions\ListingFactory $autoActionsListingFactory;
    private \M2E\Kaufland\Model\Listing\Auto\Actions\Mode\DuplicateProducts $duplicateProducts;
    private \Magento\Store\Model\StoreManagerInterface $storeManager;
    private \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory;
    private \M2E\Kaufland\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \Magento\Catalog\Model\Product $magentoProduct,
        \M2E\Kaufland\Model\Listing\Auto\Actions\ListingFactory $autoActionsListingFactory,
        \M2E\Kaufland\Model\Listing\Auto\Actions\Mode\DuplicateProducts $duplicateProducts,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->magentoProduct = $magentoProduct;
        $this->autoActionsListingFactory = $autoActionsListingFactory;
        $this->duplicateProducts = $duplicateProducts;
        $this->storeManager = $storeManager;
        $this->activeRecordFactory = $activeRecordFactory;
        $this->listingCollectionFactory = $listingCollectionFactory;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function synchWithAddedWebsiteId($websiteId)
    {
        if ($websiteId == 0) {
            $storeIds = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        } else {
            $websiteObject = $this->storeManager->getWebsite($websiteId);
            $storeIds = $websiteObject->getStoreIds();
        }

        if (count($storeIds) <= 0) {
            return;
        }

        $collection = $this->listingCollectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_MODE,
            ['eq' => \M2E\Kaufland\Model\Listing::AUTO_MODE_WEBSITE]
        );
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_WEBSITE_ADDING_MODE,
            ['neq' => \M2E\Kaufland\Model\Listing::ADDING_MODE_NONE]
        );
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_STORE_ID,
            ['in' => $storeIds]
        );

        foreach ($collection->getItems() as $listing) {
            if (!$listing->isAutoWebsiteAddingAddNotVisibleYes()) {
                if (
                    $this->magentoProduct->getVisibility(
                    ) == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
                ) {
                    continue;
                }
            }

            if ($this->duplicateProducts->checkDuplicateListingProduct($listing, $this->magentoProduct)) {
                continue;
            }

            $autoActionListing = $this->autoActionsListingFactory->create($listing);
            $autoActionListing->addProductByWebsiteListing(
                $this->magentoProduct,
                $listing
            );
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function synchWithDeletedWebsiteId($websiteId)
    {
        /** @var \Magento\Store\Model\Website $websiteObject */
        $websiteObject = $this->storeManager->getWebsite($websiteId);
        $storeIds = $websiteObject->getStoreIds();

        if (count($storeIds) <= 0) {
            return;
        }

        $collection = $this->listingCollectionFactory->create();

        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_MODE,
            ['eq' => \M2E\Kaufland\Model\Listing::AUTO_MODE_WEBSITE]
        );
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_WEBSITE_DELETING_MODE,
            ['neq' => \M2E\Kaufland\Model\Listing::DELETING_MODE_NONE]
        );

        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_STORE_ID,
            ['in' => $storeIds]
        );

        foreach ($collection->getItems() as $listing) {
            $autoActionListing = $this->autoActionsListingFactory->create($listing);
            $autoActionListing->deleteProduct(
                $this->magentoProduct,
                $listing->getAutoWebsiteDeletingMode()
            );
        }
    }
}
