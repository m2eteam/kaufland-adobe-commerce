<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Order\ShippingAddress;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractOrder;

class Edit extends AbstractOrder
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
        $this->orderResource->load($order, $orderId);

        $form = $this
            ->getLayout()
            ->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Order\Edit\ShippingAddress\Form::class, '', [
                'order' => $order,
            ]);

        $this->setAjaxContent($form->toHtml());

        return $this->getResult();
    }
}
