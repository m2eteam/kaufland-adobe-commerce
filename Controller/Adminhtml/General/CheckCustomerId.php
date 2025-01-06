<?php

namespace M2E\Kaufland\Controller\Adminhtml\General;

class CheckCustomerId extends \M2E\Kaufland\Controller\Adminhtml\AbstractGeneral
{
    /** @var \Magento\Customer\Model\Customer */
    private $customerModel;

    public function __construct(
        \Magento\Customer\Model\Customer $customerModel,
        \M2E\Kaufland\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->customerModel = $customerModel;
    }

    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id');

        $this->setJsonContent([
            'ok' => (bool)$this->customerModel->load($customerId)->getId(),
        ]);

        return $this->getResult();
    }
}
