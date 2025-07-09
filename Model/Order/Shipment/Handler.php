<?php

namespace M2E\Kaufland\Model\Order\Shipment;

use M2E\Kaufland\Model\Order\Shipment\ItemToShipLoader\ItemToShipLoaderFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection as TrackCollection;

class Handler extends \M2E\Kaufland\Model\AbstractModel
{
    public const HANDLE_RESULT_FAILED = -1;
    public const HANDLE_RESULT_SKIPPED = 0;
    public const HANDLE_RESULT_SUCCEEDED = 1;

    public const CUSTOM_CARRIER_CODE = 'custom';

    private \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory;
    private ItemToShipLoaderFactory $itemToShipLoaderFactory;

    public function __construct(
        ItemToShipLoaderFactory $itemToShipLoaderFactory,
        \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->carrierFactory = $carrierFactory;
        $this->itemToShipLoaderFactory = $itemToShipLoaderFactory;
    }

    /**
     * @param \M2E\Kaufland\Model\Order $order
     * @param \Magento\Sales\Model\Order\Shipment\Item $shipmentItem
     *
     * @return \M2E\Kaufland\Model\Order\Shipment\ItemToShipLoaderInterface
     */
    private function getItemToShipLoader(
        \M2E\Kaufland\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment\Item $shipmentItem
    ): ItemToShipLoaderInterface {
        return $this->itemToShipLoaderFactory->createDefault($order, $shipmentItem);
    }

    //########################################

    /**
     * @param \M2E\Kaufland\Model\Order $order
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     *
     * @return int
     * @throws \Exception
     */
    public function handle(
        \M2E\Kaufland\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment $shipment
    ): int {
        $trackingDetails = $this->getTrackingDetails($order, $shipment);
        if (!$this->isNeedToHandle($order)) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        $allowedItems = [];
        $items = [];
        /** @var \Magento\Sales\Model\Order\Shipment\Item $shipmentItem */
        foreach ($shipment->getAllItems() as $shipmentItem) {
            $orderItem = $shipmentItem->getOrderItem();
            if ($orderItem->getParentItemId() !== null) {
                continue;
            }

            $allowedItems[] = $orderItem->getId();

            $item = $this->getItemToShipLoader($order, $shipmentItem)->loadItem();
            if (empty($item)) {
                continue;
            }

            $items += $item;
        }

        $resultItems = [];
        foreach ($items as $orderItemId => $orderItems) {
            if (!in_array($orderItemId, $allowedItems)) {
                continue;
            }

            $resultItems = array_merge($resultItems, $orderItems);
        }

        return $this->processStatusUpdates($order, $resultItems, $trackingDetails)
            ? self::HANDLE_RESULT_SUCCEEDED
            : self::HANDLE_RESULT_FAILED;
    }

    /**
     * @param \M2E\Kaufland\Model\Order $order
     * @param \Magento\Sales\Model\Order\Shipment\Item $shipmentItem
     *
     * @return int
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function handleItem(
        \M2E\Kaufland\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment\Item $shipmentItem
    ): int {
        $trackingDetails = $this->getTrackingDetails($order, $shipmentItem->getShipment());
        if (!$this->isNeedToHandle($order)) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        $items = $this->getItemToShipLoader($order, $shipmentItem)->loadItem();

        return $this->processStatusUpdates($order, $items[$shipmentItem->getOrderItem()->getId()] ?? [], $trackingDetails)
            ? self::HANDLE_RESULT_SUCCEEDED
            : self::HANDLE_RESULT_FAILED;
    }

    //########################################

    /**
     * @param \M2E\Kaufland\Model\Order $order
     * @param array $items
     * @param \M2E\Kaufland\Model\Order\Shipment\Data\TrackingDetails|null $trackingDetails
     *
     * @return bool
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function processStatusUpdates(
        \M2E\Kaufland\Model\Order $order,
        array $items,
        ?\M2E\Kaufland\Model\Order\Shipment\Data\TrackingDetails $trackingDetails = null
    ): bool {
        if (empty($items)) {
            return false;
        }

        return $order->updateShippingStatus($trackingDetails, $items);
    }

    //########################################

    protected function getTrackingDetails(
        \M2E\Kaufland\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment $shipment
    ): ?\M2E\Kaufland\Model\Order\Shipment\Data\TrackingDetails {
        $tracks = $shipment->getTracks();
        if (empty($tracks)) {
            $tracks = $shipment->getTracksCollection();
        }

        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        if ($tracks instanceof TrackCollection) {
            $track = $tracks->getLastItem();
        } else {
            $track = end($tracks);
        }

        $number = trim((string)$track->getNumber());
        if (empty($number)) {
            return null;
        }

        $carrierCode = $carrierTitle = trim((string)$track->getCarrierCode());
        $carrier = $this->carrierFactory->create($carrierCode, $order->getStoreId());
        if ($carrier) {
            $carrierTitle = $carrier->getConfigData('title');
        }

        if ($carrierCode === \Magento\Sales\Model\Order\Shipment\Track::CUSTOM_CARRIER_CODE) {
            $carrierTitle = $track->getTitle();
        }

        return new \M2E\Kaufland\Model\Order\Shipment\Data\TrackingDetails(
            $carrierCode,
            $carrierTitle,
            trim((string)$track->getTitle()),
            $number
        );
    }

    protected function isNeedToHandle(\M2E\Kaufland\Model\Order $order): bool
    {
        return $order->canUpdateShippingStatus();
    }

    //########################################
}
