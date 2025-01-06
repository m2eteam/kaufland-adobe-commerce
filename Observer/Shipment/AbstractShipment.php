<?php

namespace M2E\Kaufland\Observer\Shipment;

abstract class AbstractShipment extends \M2E\Kaufland\Observer\AbstractObserver
{
    protected \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory;
    protected \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
    ) {
        parent::__construct($activeRecordFactory, $modelFactory);

        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->globalDataHelper = $globalDataHelper;
    }

    //########################################

    /**
     * @param \Magento\Sales\Model\Order\Shipment\Item|\Magento\Sales\Model\Order\Shipment\Track $source
     *
     * @return \Magento\Sales\Model\Order\Shipment|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getShipment($source): ?\Magento\Sales\Model\Order\Shipment
    {
        $shipment = $source->getShipment();
        if ($shipment != null && $shipment->getId()) {
            return $shipment;
        }

        $shipmentCollection = $this->shipmentCollectionFactory->create();
        $shipmentCollection->addFieldToFilter('entity_id', $source->getParentId());

        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $shipmentCollection->getFirstItem();
        if ($shipment->isObjectNew()) {
            return null;
        }

        return $shipment;
    }
}
