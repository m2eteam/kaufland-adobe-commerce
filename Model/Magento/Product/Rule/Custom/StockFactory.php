<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product\Rule\Custom;

use M2E\Kaufland\Model\Magento\Product\Rule\Custom\Stock;

class StockFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Stock
    {
        return $this->objectManager->create(Stock::class);
    }
}
