<?php

namespace M2E\Kaufland\Model\Kaufland\Order\Shipment\ItemToShipLoader;

use M2E\Kaufland\Model\Order\Shipment\ItemToShipLoaderInterface;

class DefaultObject implements ItemToShipLoaderInterface
{
    private \M2E\Kaufland\Model\Order $order;
    private \Magento\Sales\Model\Order\Shipment\Item $shipmentItem;

    public function __construct(
        \M2E\Kaufland\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment\Item $shipmentItem
    ) {
        $this->order = $order;
        $this->shipmentItem = $shipmentItem;
    }

    /**
     * @return \M2E\Kaufland\Model\Order\Item[]
     */
    public function loadItem(): array
    {
        $items = $this->loadItems();
        if (empty($items)) {
            return [];
        }

        return [$this->shipmentItem->getOrderItem()->getId() => $items];
    }

    /**
     * @return \M2E\Kaufland\Model\Order\Item[]
     */
    private function loadItems(): array
    {
        $magentoProductId = (int)$this->shipmentItem->getProductId();
        $qty = $this->shipmentItem->getQty();

        $result = [];
        foreach ($this->order->getItems() as $item) {
            if ($qty === 0) {
                break;
            }

            if ($magentoProductId !== $item->getMagentoProductId()) {
                continue;
            }

            if (!$item->canUpdateShippingStatus()) {
                continue;
            }

            $result[] = $item;
            $qty--;
        }

        return $result;
    }
}
