<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Actions;

class GlobalMode
{
    private \Magento\Catalog\Model\Product $magentoProduct;
    private \M2E\Kaufland\Model\Listing\Auto\Actions\ListingFactory $autoActionsListingFactory;
    private \M2E\Kaufland\Model\Listing\Auto\Actions\Mode\DuplicateProducts $duplicateProducts;
    private \M2E\Kaufland\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;

    public function __construct(
        \Magento\Catalog\Model\Product $magentoProduct,
        \M2E\Kaufland\Model\Listing\Auto\Actions\ListingFactory $autoActionsListingFactory,
        \M2E\Kaufland\Model\Listing\Auto\Actions\Mode\DuplicateProducts $duplicateProducts,
        \M2E\Kaufland\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory
    ) {
        $this->magentoProduct = $magentoProduct;
        $this->autoActionsListingFactory = $autoActionsListingFactory;
        $this->duplicateProducts = $duplicateProducts;
        $this->listingCollectionFactory = $listingCollectionFactory;
    }

    public function synch(): void
    {
        $collection = $this->listingCollectionFactory->create();

        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_MODE,
            \M2E\Kaufland\Model\Listing::AUTO_MODE_GLOBAL
        );
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_GLOBAL_ADDING_MODE,
            ['neq' => \M2E\Kaufland\Model\Listing::ADDING_MODE_NONE]
        );

        foreach ($collection->getItems() as $listing) {
            if (!$listing->isAutoGlobalAddingAddNotVisibleYes()) {
                if ($this->magentoProduct->getVisibility() == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE) {
                    continue;
                }
            }

            if ($this->duplicateProducts->checkDuplicateListingProduct($listing, $this->magentoProduct)) {
                continue;
            }

            $autoActionListing = $this->autoActionsListingFactory->create($listing);
            $autoActionListing->addProductByGlobalListing(
                $this->magentoProduct,
                $listing
            );
        }
    }
}
