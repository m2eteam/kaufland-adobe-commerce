<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template;

class ShippingFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Shipping
    {
        return $this->objectManager->create(Shipping::class);
    }
}
