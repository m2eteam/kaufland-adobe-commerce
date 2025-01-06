<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

class SkuGeneratorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Listing\Settings\Sku $skuSettings
    ): SkuGenerator {
        return $this->objectManager->create(SkuGenerator::class, [
            'product' => $product,
            'skuSettings' => $skuSettings,
        ]);
    }
}
