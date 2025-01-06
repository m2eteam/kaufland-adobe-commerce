<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Policy;

class ShippingDataProviderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }
    public function createShipping(
        \M2E\Kaufland\Model\Template\Shipping $shipping,
        \M2E\Kaufland\Model\Product $product
    ): ShippingDataProvider {
        return $this->objectManager->create(ShippingDataProvider::class, [
            'shipping' => $shipping,
            'product' => $product,
        ]);
    }
}
