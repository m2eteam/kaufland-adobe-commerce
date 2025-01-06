<?php

namespace M2E\Kaufland\Model\Kaufland\Listing\Product;

class PriceCalculatorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): PriceCalculator
    {
        return $this->objectManager->create(PriceCalculator::class);
    }
}
