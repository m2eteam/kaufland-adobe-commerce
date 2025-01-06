<?php

namespace M2E\Kaufland\Observer\Shipment;

class Item extends \M2E\Kaufland\Observer\Shipment\AbstractShipment
{
    private \M2E\Kaufland\Model\Order\Repository $repository;
    private \M2E\Kaufland\Helper\Module\Logger $moduleLogger;
    private \M2E\Kaufland\Model\Order\Shipment\Handler $shipmentHandler;

    public function __construct(
        \M2E\Kaufland\Model\Order\Repository $repository,
        \M2E\Kaufland\Helper\Module\Logger $moduleLogger,
        \M2E\Kaufland\Model\Order\Shipment\Handler $shipmentHandler,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
    ) {
        parent::__construct(
            $globalDataHelper,
            $activeRecordFactory,
            $modelFactory,
            $shipmentCollectionFactory,
        );
        $this->repository = $repository;
        $this->moduleLogger = $moduleLogger;
        $this->shipmentHandler = $shipmentHandler;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function process(): void
    {
        if ($this->globalDataHelper->getValue('skip_shipment_observer')) {
            return;
        }

        /** @var \Magento\Sales\Model\Order\Shipment\Item $shipmentItem */
        $shipmentItem = $this->getEvent()->getShipmentItem();
        $shipment = $this->getShipment($shipmentItem);

        if ($shipment === null) {
            $class = get_class($this);
            $this->moduleLogger->process(
                [],
                "Kaufland observer $class cannot get shipment data from event or database",
            );

            return;
        }

        /**
         * Due to task m2e-team/m2e-pro/backlog#3421 this event observer can be called two times.
         * If first time was successful, second time will be skipped.
         * "Successful" means "$shipment variable is not null".
         * There is code that looks same below, but event keys and logic are different.
         */
        $eventKey = 'skip_shipment_item_' . $shipmentItem->getId();
        if ($this->globalDataHelper->getValue($eventKey)) {
            return;
        }

        $this->globalDataHelper->setValue($eventKey, true);

        /**
         * We can catch two the same events: save of \Magento\Sales\Model\Order\Shipment\Item and
         * \Magento\Sales\Model\Order\Shipment\Track. So we must skip a duplicated one.
         * Possible situations:
         * 1. Shipment without tracks was created for Magento order. Only 'Item' observer will be called.
         * 2. Shipment with track(s) was created for Magento order. Both 'Item' and 'Track' observers will be called.
         * 3. New track(s) was added for existing shipment. Only 'Track' observer will be called.
         */
        $objectHash = spl_object_hash($shipment->getTracksCollection()->getLastItem());
        $eventKey = 'skip_' . $shipment->getId() . '##' . $objectHash;
        if (!$this->globalDataHelper->getValue($eventKey)) {
            $this->globalDataHelper->setValue($eventKey, true);
        }

        $magentoOrderId = (int)$shipment->getOrderId();

        try {
            $order = $this->repository->findByMagentoOrderId($magentoOrderId);
        } catch (\Throwable $e) {
            return;
        }

        if ($order === null) {
            return;
        }

        $order->getLogService()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_EXTENSION);

        $this->shipmentHandler->handleItem($order, $shipmentItem);
    }
}
