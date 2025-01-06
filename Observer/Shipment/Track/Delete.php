<?php

namespace M2E\Kaufland\Observer\Shipment\Track;

class Delete extends \M2E\Kaufland\Observer\Shipment\AbstractShipment
{
    private \M2E\Kaufland\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory;
    private \M2E\Kaufland\Model\Order\Repository $repository;
    private \M2E\Kaufland\Helper\Module\Logger $helperLogger;

    public function __construct(
        \M2E\Kaufland\Helper\Module\Logger $helperLogger,
        \M2E\Kaufland\Model\Order\Repository $repository,
        \M2E\Kaufland\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
    ) {
        parent::__construct(
            $globalDataHelper,
            $activeRecordFactory,
            $modelFactory,
            $shipmentCollectionFactory
        );
        $this->orderChangeCollectionFactory = $orderChangeCollectionFactory;
        $this->repository = $repository;
        $this->helperLogger = $helperLogger;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        $track = $this->getEvent()->getTrack();

        $shipment = $this->getShipment($track);

        if (!$shipment) {
            $class = get_class($this);
            $this->helperLogger->process(
                [],
                "Kaufland observer $class cannot get shipment data from event or database"
            );

            return;
        }

        $magentoOrderId = (int)$shipment->getOrderId();

        try {
            $order = $this->repository->findByMagentoOrderId($magentoOrderId);
        } catch (\Throwable $throwable) {
            return;
        }

        if ($order === null) {
            return;
        }

        $orderChangeCollection = $this->orderChangeCollectionFactory->create();
        $orderChangeCollection->addFieldToFilter('order_id', $order->getId());
        $orderChangeCollection->addFieldToFilter('action', \M2E\Kaufland\Model\Order\Change::ACTION_UPDATE_SHIPPING);
        $orderChangeCollection->addFieldToFilter('processing_attempt_count', 0);

        foreach ($orderChangeCollection->getItems() as $orderChange) {
            $params = $orderChange->getParams();
            $trackId = $params['magento_track_id'] ?? null;
            if ($trackId === null || (int)$trackId !== (int)$track->getId()) {
                continue;
            }
            $orderChange->delete();
        }
    }

    //########################################
}
