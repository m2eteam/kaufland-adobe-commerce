<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Actions\Mode\DuplicateProducts;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Listing $listingResource;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Listing $listingResource
    ) {
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->listingResource = $listingResource;
    }

    public function getListingProductIds(
        \M2E\Kaufland\Model\Listing $listing,
        \Magento\Catalog\Model\Product $magentoProduct
    ): array {
        $collection = $this->listingProductCollectionFactory->create();

        $collection->addFieldToSelect(
            \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_ID,
            'listing_product_id'
        );
        $collection->getSelect()->join(
            ['listing' => $this->listingResource->getMainTable()],
            sprintf(
                'listing.%s = main_table.%s',
                \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_ID,
                \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_LISTING_ID
            ),
            [
                \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_ACCOUNT_ID,
                \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_STOREFRONT_ID,
            ]
        );

        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_MAGENTO_PRODUCT_ID,
            ['eq' => $magentoProduct->getId()]
        );
        $collection->addFieldToFilter(
            sprintf('listing.%s', \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_ACCOUNT_ID),
            ['eq' => $listing->getAccountId()]
        );
        $collection->addFieldToFilter(
            sprintf('listing.%s', \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_STOREFRONT_ID),
            ['eq' => $listing->getStorefrontId()]
        );

        $result = $collection->toArray();

        if ($result['totalRecords'] === 0) {
            return [];
        }

        return array_map(function ($item) {
            return (int)$item['listing_product_id'];
        }, $result['items']);
    }
}
