<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Instruction\Processor;

class Config
{
    private \M2E\Kaufland\Model\Config\Manager $configManager;

    public function __construct(\M2E\Kaufland\Model\Config\Manager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function getMaxProductsForProcess(): int
    {
        return (int)$this->configManager->getGroupValue(
            '/listing/product/instructions/cron/',
            'listings_products_per_one_time',
        );
    }
}
