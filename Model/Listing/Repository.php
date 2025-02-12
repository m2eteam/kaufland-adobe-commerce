<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing;

use M2E\Kaufland\Model\ResourceModel\Listing as ListingResource;
use M2E\Kaufland\Model\ResourceModel\Product as ListingProductResource;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private ListingResource $listingResource;
    private \M2E\Kaufland\Model\ListingFactory $listingFactory;
    private \M2E\Kaufland\Model\ResourceModel\Product\Lock $productLockResource;
    private ListingProductResource $productResource;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        ListingResource $listingResource,
        \M2E\Kaufland\Model\ListingFactory $listingFactory,
        \M2E\Kaufland\Model\ResourceModel\Product\Lock $productLockResource,
        ListingProductResource $productResource
    ) {
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->listingResource = $listingResource;
        $this->listingFactory = $listingFactory;
        $this->productLockResource = $productLockResource;
        $this->productResource = $productResource;
    }

    public function getListingsCount(): int
    {
        return $this->listingCollectionFactory->create()
                                              ->getSize();
    }

    public function get(int $id): \M2E\Kaufland\Model\Listing
    {
        $listing = $this->find($id);
        if ($listing === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Listing does not exist.');
        }

        return $listing;
    }

    public function find(int $id): ?\M2E\Kaufland\Model\Listing
    {
        $listing = $this->listingFactory->create();
        $this->listingResource->load($listing, $id);

        if ($listing->isObjectNew()) {
            return null;
        }

        return $listing;
    }

    public function save(\M2E\Kaufland\Model\Listing $listing): void
    {
        $this->listingResource->save($listing);
    }

    public function remove(\M2E\Kaufland\Model\Listing $listing): void
    {
        $this->listingResource->delete($listing);
    }

    /**
     * @return \M2E\Kaufland\Model\Listing[]
     */
    public function getAll(): array
    {
        $collection = $this->listingCollectionFactory->create();

        return array_values($collection->getItems());
    }

    // ----------------------------------------

    public function isExistListingByDescriptionPolicy(int $policyId): bool
    {
        return $this->isExistListingByPolicy(ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID, $policyId);
    }

    public function isExistListingBySellingPolicy(int $policyId): bool
    {
        return $this->isExistListingByPolicy(ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID, $policyId);
    }

    public function isExistListingByShippingPolicy(int $policyId): bool
    {
        return $this->isExistListingByPolicy(ListingResource::COLUMN_TEMPLATE_SHIPPING_ID, $policyId);
    }

    public function isExistListingBySyncPolicy(int $policyId): bool
    {
        return $this->isExistListingByPolicy(ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID, $policyId);
    }

    private function isExistListingByPolicy(
        string $columnName,
        int $policyId
    ): bool {
        $listingCollection = $this->listingCollectionFactory->create();
        $listingCollection->addFieldToFilter($columnName, ['eq' => $policyId]);

        return $listingCollection->getSize() !== 0;
    }

    public function hasProductsInSomeAction(\M2E\Kaufland\Model\Listing $listing): bool
    {
        $connection = $this->productResource->getConnection();

        $productTable = $this->productResource->getMainTable();
        $lockTable = $this->productLockResource->getMainTable();

        $select = $connection->select()
                             ->from(['p' => $productTable])
                             ->join(
                                 ['pl' => $lockTable],
                                 sprintf(
                                     'p.%s = pl.%s',
                                     \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_ID,
                                     \M2E\Kaufland\Model\ResourceModel\Product\Lock::COLUMN_PRODUCT_ID,
                                 ),
                                 []
                             )
                             ->where(
                                 sprintf('p.%s = ?', \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_LISTING_ID),
                                 $listing->getId()
                             )
                             ->limit(1);

        return (bool) $connection->fetchOne($select);
    }
}
