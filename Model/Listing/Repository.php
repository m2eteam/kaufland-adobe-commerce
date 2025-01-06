<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing;

use M2E\Kaufland\Model\ResourceModel\Listing as ListingResource;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private ListingResource $listingResource;
    private \M2E\Kaufland\Model\ListingFactory $listingFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        ListingResource $listingResource,
        \M2E\Kaufland\Model\ListingFactory $listingFactory
    ) {
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->listingResource = $listingResource;
        $this->listingFactory = $listingFactory;
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
}
