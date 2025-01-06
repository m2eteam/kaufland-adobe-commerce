<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Order;

class UpdateShippingStatus extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractOrder
{
    private \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory;
    private \M2E\Kaufland\Model\Order\Shipment\Handler $orderShipmentHandler;

    public function __construct(
        \M2E\Kaufland\Model\Order\Shipment\Handler $orderShipmentHandler,
        \M2E\Kaufland\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory
    ) {
        parent::__construct();

        $this->orderShipmentCollectionFactory = $orderShipmentCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderShipmentHandler = $orderShipmentHandler;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function execute()
    {
        $orderIds = $this->getRequestIds();

        if (count($orderIds) == 0) {
            $this->messageManager->addError(__('Please select Order(s).'));

            return false;
        }

        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter('id', ['in' => $orderIds]);

        $hasFailed = false;
        $hasSucceeded = false;

        foreach ($orderCollection->getItems() as $order) {
            $order->getLogService()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_USER);

            /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection $shipmentsCollection */
            $shipmentsCollection = $this->orderShipmentCollectionFactory->create();
            $shipmentsCollection->setOrderFilter($order->getMagentoOrderId());

            if ($shipmentsCollection->getSize() === 0) {
                $order->updateShippingStatus()
                    ? $hasSucceeded = true
                    : $hasFailed = true;
                continue;
            }

            foreach ($shipmentsCollection->getItems() as $shipment) {
                /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                if (!$shipment->getId()) {
                    continue;
                }

                $result = $this->orderShipmentHandler->handle($order, $shipment);

                $result == \M2E\Kaufland\Model\Order\Shipment\Handler::HANDLE_RESULT_SUCCEEDED
                    ? $hasSucceeded = true
                    : $hasFailed = true;
            }
        }

        if (!$hasFailed && $hasSucceeded) {
            $this->messageManager->addSuccess(
                __('Updating Order(s) Status to Shipped in Progress...')
            );
        } elseif ($hasFailed && !$hasSucceeded) {
            $this->messageManager->addError(
                __('Order(s) can not be updated for Shipped Status.')
            );
        } elseif ($hasFailed && $hasSucceeded) {
            $this->messageManager->addError(
                __('Some of Order(s) can not be updated for Shipped Status.')
            );
        }

        return $this->_redirect($this->redirect->getRefererUrl());
    }
}
