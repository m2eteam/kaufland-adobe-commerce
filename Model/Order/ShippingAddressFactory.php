<?php

namespace M2E\Kaufland\Model\Order;

class ShippingAddressFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\Kaufland\Model\Order $order, array $data = []): ShippingAddress
    {
        $data['order'] = $order;

        return $this->objectManager->create(ShippingAddress::class, $data);
    }
}
