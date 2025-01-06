<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product;

class CacheFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Cache
    {
        return $this->objectManager->create(Cache::class);
    }
}
