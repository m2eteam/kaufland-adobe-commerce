<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Wizard\InstallationKaufland;

class AccountCreate extends Installation
{
    private \M2E\Kaufland\Helper\Module\Exception $exceptionHelper;
    private \M2E\Kaufland\Model\Account\Create $accountCreate;
    private \M2E\Kaufland\Helper\View\Configuration $configurationHelper;
    private \M2E\Core\Model\LicenseService $licenseService;

    public function __construct(
        \M2E\Kaufland\Helper\Module\Exception $exceptionHelper,
        \M2E\Kaufland\Model\Account\Create $accountCreate,
        \M2E\Kaufland\Helper\View\Configuration $configurationHelper,
        \M2E\Core\Helper\Magento $magentoHelper,
        \M2E\Kaufland\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \M2E\Core\Model\LicenseService $licenseService
    ) {
        parent::__construct($magentoHelper, $wizardHelper, $nameBuilder, $licenseService);
        $this->exceptionHelper = $exceptionHelper;
        $this->accountCreate = $accountCreate;
        $this->configurationHelper = $configurationHelper;
        $this->licenseService = $licenseService;
    }

    public function execute()
    {
        $clientKey = $this->getRequest()->getPost('client_key');
        $secretKey = $this->getRequest()->getPost('secret_key');
        $title = $this->getRequest()->getPost('title');

        if (empty($clientKey) || empty($secretKey)) {
            $this->_forward('index');
        }

        try {
            $this->accountCreate->create((string)$title, (string)$clientKey, (string)$secretKey);
            $this->setStep($this->getNextStep());
        } catch (\Throwable $exception) {
            $this->exceptionHelper->process($exception);

            if (
                !$this->licenseService->get()->getInfo()->getDomainIdentifier()->isValid()
                || !$this->licenseService->get()->getInfo()->getIpIdentifier()->isValid()
            ) {
                $error = __(
                    'The %channel_title access obtaining is currently unavailable.<br/>Reason: %error_message
</br>Go to the <a href="%url" target="_blank">License Page</a>.',
                    [
                        'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                        'error_message' => $exception->getMessage(),
                        'url' => $this->configurationHelper->getLicenseUrl(['wizard' => 1]),
                    ],
                );
            } else {
                $error = __(
                    'The %channel_title access obtaining is currently unavailable.<br/>Reason: %error_message',
                    [
                        'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                        'error_message' => $exception->getMessage()
                    ]
                );
            }

            $this->setJsonContent(['message' => $error]);

            return $this->getResult();
        }

        $this->setJsonContent([
            'url' => $this->getUrl('*/*/installation'),
        ]);

        return $this->getResult();
    }
}
