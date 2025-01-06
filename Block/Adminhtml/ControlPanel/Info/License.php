<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\ControlPanel\Info;

use M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock;

class License extends AbstractBlock
{
    private \M2E\Core\Helper\Client $clientHelper;
    private \M2E\Core\Helper\Module $moduleHelper;
    /** @var array */
    public array $licenseData = [];
    /** @var array */
    public array $locationData = [];
    private \M2E\Core\Model\LicenseService $licenseService;

    public function __construct(
        \M2E\Core\Helper\Client $clientHelper,
        \M2E\Core\Helper\Module $moduleHelper,
        \M2E\Core\Model\LicenseService $licenseService,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->clientHelper = $clientHelper;
        $this->moduleHelper = $moduleHelper;
        $this->licenseService = $licenseService;
        parent::__construct($context, $data);
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('controlPanelInfoLicense');
        $this->setTemplate('control_panel/info/license.phtml');
    }

    protected function _beforeToHtml()
    {
        $license = $this->licenseService->get();
        $domainIdentifier = $license->getInfo()->getDomainIdentifier();
        $ipIdentifier = $license->getInfo()->getIpIdentifier();

        $this->licenseData = [
            'key' => \M2E\Core\Helper\Data::escapeHtml($license->getKey()),

            'domain' => \M2E\Core\Helper\Data::escapeHtml($domainIdentifier->getValidValue()),
            'ip' => \M2E\Core\Helper\Data::escapeHtml($ipIdentifier->getValidValue()),
            'valid' => [
                'domain' => $domainIdentifier->isValid(),
                'ip' => $ipIdentifier->isValid(),
            ],
        ];

        $this->locationData = [
            'domain' => $this->clientHelper->getDomain(),
            'ip' => $this->clientHelper->getIp(),
            'directory' => $this->clientHelper->getBaseDirectory(),
            'relative_directory' => $this->moduleHelper->getBaseRelativeDirectory(
                \M2E\Kaufland\Helper\Module::IDENTIFIER
            ),
        ];

        return parent::_beforeToHtml();
    }
}
