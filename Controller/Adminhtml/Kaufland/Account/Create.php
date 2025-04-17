<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Account;

class Create extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractAccount
{
    private \M2E\Kaufland\Model\Account\Create $accountCreate;

    public function __construct(
        \M2E\Kaufland\Model\Account\Create $accountCreate
    ) {
        parent::__construct();

        $this->accountCreate = $accountCreate;
    }

    public function execute()
    {
        $clientKey = $this->getRequest()->getPost('client_key');
        $secretKey = $this->getRequest()->getPost('secret_key');
        $title = $this->getRequest()->getPost('title');

        if (empty($clientKey) || empty($secretKey)) {
            $this->messageManager->addErrorMessage(__('Please complete all required fields before saving the configurations.'));
            $this->setJsonContent(
                [
                    'result' => false,
                    'redirectUrl' => $this->_redirect->getRefererUrl()
                ]
            );

            return $this->getResult();
        }

        try {
            $account = $this->accountCreate->create($title, (string)$clientKey, (string)$secretKey);
        } catch (\Throwable $e) {
            $message = (string)__(
                'The %channel_title access obtaining is currently unavailable.<br/>Reason: %error_message',
                [
                    'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                    'error_message' => $e->getMessage()
                ],
            );

            $this->messageManager->addError($message);
            $this->setJsonContent(
                [
                    'result' => false,
                    'redirectUrl' => $this->_redirect->getRefererUrl()
                ]
            );

            return $this->getResult();
        }

        $this->messageManager->addSuccessMessage(__('Account was created'));
        $this->setJsonContent(
            [
                'result' => true,
                'redirectUrl' => $this->getUrl('*/kaufland_account/edit', ['id' => $account->getId()])
            ]
        );

        return $this->getResult();
    }
}
