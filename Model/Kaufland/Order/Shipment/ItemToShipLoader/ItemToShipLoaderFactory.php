<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Order\Shipment\ItemToShipLoader;

class ItemToShipLoaderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createDefault(
        \M2E\Kaufland\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment\Item $shipmentItem
    ): DefaultObject {
        return $this->objectManager->create(
            DefaultObject::class,
            [
                'order' => $order,
                'shipmentItem' => $shipmentItem,
            ],
        );
    }
}
