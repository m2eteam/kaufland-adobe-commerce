<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\SellingFormat;

class AffectedListingsProducts extends \M2E\Kaufland\Model\Template\AffectedListingsProductsAbstract
{
    private \M2E\Kaufland\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\Kaufland\Model\Product\Repository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    public function loadListingProductCollection(
        array $filters = []
    ): \M2E\Kaufland\Model\ResourceModel\Product\Collection {
        return $this->productRepository->createCollectionByListingSellingPolicy(
            (int)$this->getModel()->getId()
        );
    }
}