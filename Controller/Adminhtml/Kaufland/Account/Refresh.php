<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Account;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractAccount;

class Refresh extends AbstractAccount
{
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\Account\Update $accountUpdate;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Account\Update $accountUpdate
    ) {
        parent::__construct();
        $this->accountRepository = $accountRepository;
        $this->accountUpdate = $accountUpdate;
    }

    public function execute(): void
    {
        $id = $this->getRequest()->getParam('id');

        $account = $this->accountRepository->find((int)$id);
        if ($account === null) {
            $this->messageManager->addErrorMessage(__('Account is not found and cannot be refreshed.'));

            $this->_redirect('*/*/index');

            return;
        }

        try {
            $this->accountUpdate->updateStorefronts($account);
            $this->messageManager->addSuccessMessage(__('Account was refreshed.'));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('The account data failed to be updated, please try to refresh it again.'));
        }

        $this->_redirect('*/*/index');
    }
}
