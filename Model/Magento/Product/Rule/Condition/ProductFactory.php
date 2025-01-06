<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product\Rule\Condition;

use M2E\Kaufland\Model\Magento\Product\Rule\Condition\Product;

class ProductFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Product
    {
        return $this->objectManager->create(Product::class);
    }
}
