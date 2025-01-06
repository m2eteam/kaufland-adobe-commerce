<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model;

class ShippingGroupFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): ShippingGroup
    {
        return $this->objectManager->create(ShippingGroup::class);
    }
}
