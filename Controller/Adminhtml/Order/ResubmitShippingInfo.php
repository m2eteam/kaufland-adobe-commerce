<?php

namespace M2E\Kaufland\Controller\Adminhtml\Order;

class ResubmitShippingInfo extends \M2E\Kaufland\Controller\Adminhtml\AbstractOrder
{
    private \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory;
    private \M2E\Kaufland\Model\OrderFactory $orderFactory;
    private \M2E\Kaufland\Model\ResourceModel\Order $orderResource;
    private \M2E\Kaufland\Model\Order\Shipment\Handler $orderShipmentHandler;

    public function __construct(
        \M2E\Kaufland\Model\Order\Shipment\Handler $orderShipmentHandler,
        \M2E\Kaufland\Model\OrderFactory $orderFactory,
        \M2E\Kaufland\Model\ResourceModel\Order $orderResource,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory,
        \M2E\Kaufland\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->orderShipmentCollectionFactory = $orderShipmentCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
        $this->orderShipmentHandler = $orderShipmentHandler;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $orderIds = $this->getRequestIds();

        $isFail = false;

        foreach ($orderIds as $orderId) {
            $order = $this->orderFactory->create();
            $this->orderResource->load($order, $orderId);

            $order->getLogService()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_USER);

            $shipmentsCollection = $this->orderShipmentCollectionFactory->create();
            $shipmentsCollection->setOrderFilter($order->getMagentoOrderId());

            foreach ($shipmentsCollection->getItems() as $shipment) {
                /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                if (!$shipment->getId()) {
                    continue;
                }

                $result = $this->orderShipmentHandler->handle($order, $shipment);

                if ($result == \M2E\Kaufland\Model\Order\Shipment\Handler::HANDLE_RESULT_FAILED) {
                    $isFail = true;
                }
            }
        }

        if ($isFail) {
            $errorMessage = __('Shipping Information was not resend.');
            if (count($orderIds) > 1) {
                $errorMessage = __('Shipping Information was not resend for some Orders.');
            }

            $this->messageManager->addError($errorMessage);
        } else {
            $this->messageManager->addSuccess(
                __('Shipping Information has been resend.')
            );
        }

        return $this->_redirect($this->redirect->getRefererUrl());
    }
}
