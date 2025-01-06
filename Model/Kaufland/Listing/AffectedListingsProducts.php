<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing;

class AffectedListingsProducts extends \M2E\Kaufland\Model\Template\AffectedListingsProductsAbstract
{
    private \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory
    ) {
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function loadListingProductCollection(
        array $filters = []
    ): \M2E\Kaufland\Model\ResourceModel\Product\Collection {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_LISTING_ID,
            $this->getModel()->getId()
        );

        if (isset($filters['template'])) {
            $template = $filters['template'];

            if ($template === \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SELLING_FORMAT) {
                $collection->addFieldToFilter(
                    \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_TEMPLATE_SELLING_FORMAT_MODE,
                    ['eq' => \M2E\Kaufland\Model\Kaufland\Template\Manager::MODE_PARENT]
                );
            }

            if ($template === \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SYNCHRONIZATION) {
                $collection->addFieldToFilter(
                    \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_TEMPLATE_SYNCHRONIZATION_MODE,
                    ['eq' => \M2E\Kaufland\Model\Kaufland\Template\Manager::MODE_PARENT]
                );
            }
        }

        return $collection;
    }
}
