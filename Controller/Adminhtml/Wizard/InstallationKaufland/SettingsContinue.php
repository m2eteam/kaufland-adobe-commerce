<?php

namespace M2E\Kaufland\Controller\Adminhtml\Wizard\InstallationKaufland;

class SettingsContinue extends Installation
{
    private \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration;

    public function __construct(
        \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration,
        \M2E\Core\Helper\Magento $magentoHelper,
        \M2E\Kaufland\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \M2E\Core\Model\LicenseService $licenseService
    ) {
        parent::__construct(
            $magentoHelper,
            $wizardHelper,
            $nameBuilder,
            $licenseService
        );
        $this->configuration = $configuration;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (empty($params)) {
            return $this->indexAction();
        }

        $this->configuration->setConfigValues($params);
        $this->setStep($this->getNextStep());

        return $this->_redirect('*/*/installation');
    }
}
