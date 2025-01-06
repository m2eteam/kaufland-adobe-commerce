<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Order;

class OrderItemGrid extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractOrder
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
        $orderId = $this->getRequest()->getParam('id');

        $order = $this->orderFactory->create();
        $this->orderResource->load($order, (int)$orderId);

        if ($order->isObjectNew()) {
            $this->setJsonContent([
                'error' => __('Please specify Required Options.'),
            ]);

            return $this->getResult();
        }

        $orderItemsBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Order\View\Item::class, '', [
                'order' => $order,
            ]);

        $this->setAjaxContent($orderItemsBlock->toHtml());

        return $this->getResult();
    }
}
