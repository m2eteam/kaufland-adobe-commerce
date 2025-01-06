<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

use M2E\Kaufland\Model\Product\PriceCalculator;

class PriceCalculatorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\Kaufland\Model\Product $product): PriceCalculator
    {
        return $this->objectManager->create(PriceCalculator::class, ['product' => $product]);
    }
}
