<?php

namespace M2E\Kaufland\Observer\Order;

class View extends \M2E\Kaufland\Observer\AbstractObserver
{
    protected \Magento\Customer\Model\CustomerFactory $customerFactory;
    protected \Magento\Framework\Registry $registry;
    private \Magento\Customer\Model\ResourceModel\Customer $customerResource;
    private \M2E\Kaufland\Model\Order\Repository $repository;

    public function __construct(
        \M2E\Kaufland\Model\Order\Repository $repository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Framework\Registry $registry,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory
    ) {
        parent::__construct($activeRecordFactory, $modelFactory);
        $this->customerFactory = $customerFactory;
        $this->registry = $registry;
        $this->customerResource = $customerResource;
        $this->repository = $repository;
    }

    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order $magentoOrder */
        $magentoOrder = $this->registry->registry('current_order');
        if (empty($magentoOrder) || !$magentoOrder->getId()) {
            return;
        }

        try {
            $order = $this->repository->findByMagentoOrderId((int)$magentoOrder->getId());
        } catch (\Throwable $exception) {
            return;
        }

        if ($order === null) {
            return;
        }

        $customerId = $magentoOrder->getCustomerId();
        if (empty($customerId) || $magentoOrder->getCustomerIsGuest()) {
            return;
        }

        $customer = $this->customerFactory->create();
        $this->customerResource->load($customer, $customerId);

        $magentoOrder->setData(
            'customer_' . \M2E\Kaufland\Model\Order\ProxyObject::USER_ID_ATTRIBUTE_CODE,
            $customer->getData(\M2E\Kaufland\Model\Order\ProxyObject::USER_ID_ATTRIBUTE_CODE)
        );
    }
}
