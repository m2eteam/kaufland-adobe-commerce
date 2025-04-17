<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Account;

class UpdateCredentials extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractAccount
{
    private \M2E\Kaufland\Helper\Module\Exception $helperException;
    private \M2E\Kaufland\Model\Account\Update $accountUpdate;
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Kaufland\Model\Account\Update $accountUpdate,
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Helper\Module\Exception $helperException
    ) {
        parent::__construct();

        $this->helperException = $helperException;
        $this->accountUpdate = $accountUpdate;
        $this->accountRepository = $accountRepository;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('id', 0);

        if ($accountId === 0) {
            $this->messageManager->addErrorMessage(__('Account does not exist.'));
            $this->setJsonContent(
                [
                    'result' => false,
                    'redirectUrl' => $this->_redirect->getRefererUrl()
                ]
            );

            return $this->getResult();
        }

        $account = $this->accountRepository->get($accountId);
        $clientKey = $this->getRequest()->getPost('client_key');
        $secretKey = $this->getRequest()->getPost('secret_key');

        if (empty($clientKey) || empty($secretKey)) {
            $this->messageManager->addErrorMessage(__('Please complete all required fields before saving the configurations.'));
            $this->setJsonContent(
                [
                    'result' => false,
                    'redirectUrl' => $this->getUrl('*/kaufland_account/edit', ['id' => $accountId])
                ]
            );

            return $this->getResult();
        }

        try {
            $this->accountUpdate->updateCredentials(
                $account,
                (string)$clientKey,
                (string)$secretKey,
            );
        } catch (\Throwable $exception) {
            $this->helperException->process($exception);

            $message = __(
                'The %channel_title access obtaining is currently unavailable.<br/>Reason: %error_message',
                [
                    'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                    'error_message' => $exception->getMessage()
                ],
            );

            $this->messageManager->addError($message);
            $this->setJsonContent(
                [
                    'result' => false,
                    'redirectUrl' => $this->getUrl('*/kaufland_account/edit', ['id' => $accountId])
                ]
            );

            return $this->getResult();
        }

        $this->messageManager->addSuccessMessage(__('Access Details were updated'));
        $this->setJsonContent(
            [
                'result' => true,
                'redirectUrl' => $this->getUrl('*/kaufland_account/edit', ['id' => $accountId])
            ]
        );

        return $this->getResult();
    }
}
