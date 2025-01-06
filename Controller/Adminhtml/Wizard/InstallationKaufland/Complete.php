<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Wizard\InstallationKaufland;

use M2E\Kaufland\Helper\Module\Wizard;

class Complete extends Installation
{
    private \M2E\Core\Helper\Magento $magentoHelper;

    public function __construct(
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

        $this->magentoHelper = $magentoHelper;
    }

    public function execute()
    {
        $this->magentoHelper->clearMenuCache();

        $status = $this->getRequest()->getParam('status');

        if ($status === \M2E\Kaufland\Block\Adminhtml\Wizard\InstallationKaufland\Installation\ListingTutorial::INSTALLATION_COMPLETE) {
            $this->stepCompleteStatus();
        }

        if ($status === \M2E\Kaufland\Block\Adminhtml\Wizard\InstallationKaufland\Installation\ListingTutorial::INSTALLATION_SKIP) {
            $this->stepSkipStatus();
        }
    }

    private function stepCompleteStatus()
    {
        $this->setStatus(Wizard::STATUS_COMPLETED);
        $this->_redirect("*/kaufland_listing_create/index");
    }

    private function stepSkipStatus()
    {
        $this->setStatus(Wizard::STATUS_SKIPPED);
        $this->_redirect("*/kaufland_listing/index");
    }
}
