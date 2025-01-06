<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\General;

class GetAccounts extends \M2E\Kaufland\Controller\Adminhtml\AbstractGeneral
{
    private \M2E\Kaufland\Model\Account\Repository $accountsRepository;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountsRepository
    ) {
        parent::__construct();

        $this->accountsRepository = $accountsRepository;
    }

    public function execute()
    {
        $accounts = [];
        foreach ($this->accountsRepository->getAll() as $account) {
            $accounts[] = [
                'id' => $account->getId(),
                'title' => \M2E\Core\Helper\Data::escapeHtml($account->getTitle()),
            ];
        }

        $this->setJsonContent($accounts);

        return $this->getResult();
    }
}
