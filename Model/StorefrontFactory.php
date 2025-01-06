<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model;

class StorefrontFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Storefront
    {
        return $this->objectManager->create(Storefront::class);
    }
}
