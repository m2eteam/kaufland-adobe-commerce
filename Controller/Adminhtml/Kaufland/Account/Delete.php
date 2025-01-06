<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Account;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractAccount;

class Delete extends AbstractAccount
{
    private \M2E\Kaufland\Model\Account\DeleteService $accountDelete;
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Account\DeleteService $accountDelete
    ) {
        parent::__construct();
        $this->accountDelete = $accountDelete;
        $this->accountRepository = $accountRepository;
    }

    public function execute(): void
    {
        $id = $this->getRequest()->getParam('id');

        $account = $this->accountRepository->find((int)$id);
        if ($account === null) {
            $this->messageManager->addError(__('Account is not found and cannot be deleted.'));

            $this->_redirect('*/*/index');

            return;
        }

        try {
            $this->accountDelete->delete($account);

            $this->messageManager->addSuccess(__('Account was deleted.'));
        } catch (\Exception $exception) {
            $this->messageManager->addError(__($exception->getMessage()));
        }

        $this->_redirect('*/*/index');
    }
}
