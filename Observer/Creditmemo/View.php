<?php

namespace M2E\Kaufland\Observer\Creditmemo;

class View extends \M2E\Kaufland\Observer\AbstractObserver
{
    /** @var \Magento\Customer\Model\CustomerFactory */
    protected $customerFactory;
    /** @var \Magento\Framework\Registry */
    protected $registry;
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
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $this->registry->registry('current_creditmemo');
        if (empty($creditmemo) || !$creditmemo->getId()) {
            return;
        }

        try {
            $order = $this->orderRepository->findByMagentoOrderId((int)$creditmemo->getOrderId());
        } catch (\Exception $exception) {
            return;
        }

        if (empty($order) || !$order->getId()) {
            return;
        }

        $customerId = $creditmemo->getOrder()->getCustomerId();
        if (empty($customerId) || $creditmemo->getOrder()->getCustomerIsGuest()) {
            return;
        }

        $customer = $this->customerFactory->create()->load($customerId);

        $creditmemo->getOrder()->setData(
            'customer_' . \M2E\Kaufland\Model\Order\ProxyObject::USER_ID_ATTRIBUTE_CODE,
            $customer->getData(\M2E\Kaufland\Model\Order\ProxyObject::USER_ID_ATTRIBUTE_CODE)
        );
    }
}
