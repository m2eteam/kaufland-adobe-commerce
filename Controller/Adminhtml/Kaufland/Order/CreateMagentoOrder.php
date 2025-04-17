<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Order;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractOrder;

class CreateMagentoOrder extends AbstractOrder
{
    private \M2E\Kaufland\Model\OrderFactory $orderFactory;
    private \M2E\Kaufland\Model\ResourceModel\Order $orderResource;

    public function __construct(
        \M2E\Kaufland\Model\OrderFactory $orderFactory,
        \M2E\Kaufland\Model\ResourceModel\Order $orderResource
    ) {
        parent::__construct();
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
    }

    public function execute()
    {
        $orderIds = $this->getRequestIds();
        $warnings = 0;
        $errors = 0;

        foreach ($orderIds as $orderId) {
            $order = $this->orderFactory->create();
            $this->orderResource->load($order, (int)$orderId);
            $order->getLogService()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_USER);

            if ($order->getMagentoOrderId() !== null) {
                $warnings++;
                continue;
            }

            // Create magento order
            // ---------------------------------------

            if ($order->canCreateMagentoOrder()) {
                try {
                    $order->createMagentoOrder();
                } catch (\Exception $e) {
                    $errors++;
                }
            }

            // ---------------------------------------

            if ($order->canCreateInvoice()) {
                $order->createInvoice();
            }

            $order->createShipments();

            if (!$order->getAccount()->getOrdersSettings()->isOrderStatusMappingModeDefault()) {
                $order->updateMagentoOrderStatus();
            }

            if ($order->canCreateTracks()) {
                $order->createTracks();
            }
        }

        if (!$errors && !$warnings) {
            $this->messageManager->addSuccess(__('Magento Order(s) were created.'));
        }

        if ($errors) {
            $this->messageManager->addError(
                __(
                    '%count Magento order(s) were not created. Please <a target="_blank" href="%url">view Log</a>
                for the details.',
                    ['count' => $errors, 'url' => $this->getUrl('*/Kaufland_log_order')]
                )
            );
        }

        if ($warnings) {
            $this->messageManager->addWarning(
                __(
                    '%count Magento order(s) are already created for the selected %channel_title order(s).',
                    [
                        'count' => $warnings,
                        'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                    ]
                )
            );
        }

        if (count($orderIds) == 1) {
            return $this->_redirect('*/*/view', ['id' => $orderIds[0]]);
        } else {
            return $this->_redirect($this->redirect->getRefererUrl());
        }
    }
}
