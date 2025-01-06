<?php

namespace M2E\Kaufland\Observer\Invoice;

class View extends \M2E\Kaufland\Observer\AbstractObserver
{
    /** @var \Magento\Customer\Model\CustomerFactory */
    private $customerFactory;
    /** @var \Magento\Framework\Registry */
    private $registry;
    private \M2E\Kaufland\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\Kaufland\Model\Order\Repository $orderRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Registry $registry,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory
    ) {
        parent::__construct($activeRecordFactory, $modelFactory);
        $this->customerFactory = $customerFactory;
        $this->registry = $registry;
        $this->orderRepository = $orderRepository;
    }

    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $this->registry->registry('current_invoice');
        if (empty($invoice) || !$invoice->getId()) {
            return;
        }

        try {
            $order = $this->orderRepository->findByMagentoOrderId($invoice->getOrderId());
        } catch (\Exception $exception) {
            return;
        }

        if (empty($order) || !$order->getId()) {
            return;
        }

        $customerId = $invoice->getOrder()->getCustomerId();
        if (empty($customerId) || $invoice->getOrder()->getCustomerIsGuest()) {
            return;
        }

        $customer = $this->customerFactory->create()->load($customerId);

        $invoice->getOrder()->setData(
            'customer_' . \M2E\Kaufland\Model\Order\ProxyObject::USER_ID_ATTRIBUTE_CODE,
            $customer->getData(\M2E\Kaufland\Model\Order\ProxyObject::USER_ID_ATTRIBUTE_CODE)
        );
    }
}
