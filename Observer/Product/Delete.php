<?php

declare(strict_types=1);

namespace M2E\Kaufland\Observer\Product;

class Delete extends AbstractProduct
{
    private \M2E\Kaufland\Model\ListingFactory $listingFactory;
    private \M2E\Kaufland\Model\Listing\Other\DeleteService $otherDeleteService;
    private \M2E\Kaufland\Model\Listing\Other\UnmapDeletedProduct $unmanagedUnmapDeletedProduct;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Other\UnmapDeletedProduct $unmanagedUnmapDeletedProduct,
        \M2E\Kaufland\Model\Listing\Other\DeleteService $otherDeleteService,
        \M2E\Kaufland\Model\ListingFactory $listingFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory
    ) {
        parent::__construct(
            $productFactory,
            $activeRecordFactory,
            $modelFactory
        );
        $this->unmanagedUnmapDeletedProduct = $unmanagedUnmapDeletedProduct;
        $this->listingFactory = $listingFactory;
        $this->otherDeleteService = $otherDeleteService;
    }

    public function process(): void
    {
        if (empty($this->getProductId())) {
            return;
        }

        $this->listingFactory
            ->create()
            ->removeDeletedProduct($this->getProduct());

        $this->unmanagedUnmapDeletedProduct->process($this->getProduct());
        $this->otherDeleteService->byMagentoProductId($this->getProductId());
    }
}
