<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

class RemoveHandler
{
    private \M2E\Kaufland\Model\Product\DeleteService $productDeleteService;

    public function __construct(
        \M2E\Kaufland\Model\Product\DeleteService $productDeleteService
    ) {
        $this->productDeleteService = $productDeleteService;
    }

    public function process(\M2E\Kaufland\Model\Product $listingProduct): void
    {
        if (!$listingProduct->isStatusNotListed()) {
            $listingProduct->setData('status', \M2E\Kaufland\Model\Product::STATUS_NOT_LISTED);
            $listingProduct->save();
        }

        $this->productDeleteService->process($listingProduct);
    }
}
