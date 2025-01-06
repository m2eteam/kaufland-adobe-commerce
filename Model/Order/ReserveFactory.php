<?php

namespace M2E\Kaufland\Model\Order;

class ReserveFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\Kaufland\Model\Order $order): Reserve
    {
        return $this->objectManager->create(Reserve::class, ['order' => $order]);
    }
}
