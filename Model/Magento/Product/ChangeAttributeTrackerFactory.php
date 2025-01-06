<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product;

class ChangeAttributeTrackerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Kaufland\Model\Product $listingProduct
    ): ChangeAttributeTracker {
        return $this->objectManager->create(ChangeAttributeTracker::class, [
            'listingProduct' => $listingProduct,
        ]);
    }
}
