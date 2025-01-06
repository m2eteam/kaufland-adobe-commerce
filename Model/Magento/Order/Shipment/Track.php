<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Order\Shipment;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection as TrackCollection;

class Track
{
    private ?\Magento\Sales\Model\Order $magentoOrder = null;

    private \Magento\Sales\Model\Order\Shipment\TrackFactory $shipmentTrackFactory;
    private \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\Kaufland\Model\Order $order;
    private array $trackingDetails;

    public function __construct(
        \Magento\Sales\Model\Order\Shipment\TrackFactory $shipmentTrackFactory,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Kaufland\Model\Order $order,
        array $trackingDetails
    ) {
        $this->shipmentTrackFactory = $shipmentTrackFactory;
        $this->globalDataHelper = $globalDataHelper;
        $this->order = $order;
        $this->trackingDetails = $trackingDetails;
    }

    public function getTracks(): array
    {
        return $this->prepareTracks();
    }

    // ----------------------------------------

    private function prepareTracks(): array
    {
        $trackingDetails = $this->getFilteredTrackingDetails();
        if (count($trackingDetails) == 0) {
            return [];
        }

        // Skip shipment observer
        // ---------------------------------------
        $this->globalDataHelper->unsetValue('skip_shipment_observer');
        $this->globalDataHelper->setValue('skip_shipment_observer', true);
        // ---------------------------------------

        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->getMagentoOrder()->getShipmentsCollection()->getFirstItem();

        // Sometimes Magento returns an array instead of Collection by a call of $shipment->getTracksCollection()
        if (
            $shipment->hasData(ShipmentInterface::TRACKS) &&
            !($shipment->getData(ShipmentInterface::TRACKS) instanceof TrackCollection)
        ) {
            $shipment->unsetData(ShipmentInterface::TRACKS);
        }

        $tracks = [];
        foreach ($trackingDetails as $trackingDetail) {
            $track = $this->shipmentTrackFactory->create();
            $track->setNumber($trackingDetail['tracking_number']);
            $track->setTitle($trackingDetail['shipping_provider']);
            $track->setCarrierCode($trackingDetail['shipping_carrier']);

            $shipment->addTrack($track)->save();
            $tracks[] = $track;
        }

        return $tracks;
    }

    // ---------------------------------------

    private function getFilteredTrackingDetails(): array
    {
        if ($this->getMagentoOrder()->getTracksCollection()->getSize() <= 0) {
            return $this->trackingDetails;
        }

        foreach ($this->getMagentoOrder()->getTracksCollection() as $track) {
            foreach ($this->trackingDetails as $key => $trackingDetail) {
                if (
                    strtolower($track->getData('track_number'))
                    == strtolower($trackingDetail['tracking_number'])
                ) {
                    unset($this->trackingDetails[$key]);
                }
            }
        }

        return $this->trackingDetails;
    }

    // ---------------------------------------

    private function getMagentoOrder(): ?\Magento\Sales\Model\Order
    {
        if ($this->magentoOrder !== null) {
            return $this->magentoOrder;
        }

        $this->magentoOrder = $this->order->getMagentoOrder();

        return $this->magentoOrder;
    }
}
