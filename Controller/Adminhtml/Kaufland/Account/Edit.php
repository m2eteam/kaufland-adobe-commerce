<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Account;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractAccount;

class Edit extends AbstractAccount
{
    private \M2E\Kaufland\Model\Connector\Client\Single $serverClient;
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Connector\Client\Single $serverClient
    ) {
        parent::__construct();

        $this->serverClient = $serverClient;
        $this->accountRepository = $accountRepository;
    }

    protected function getLayoutType(): string
    {
        return self::LAYOUT_TWO_COLUMNS;
    }

    public function execute()
    {
        $account = null;
        if ($id = $this->getRequest()->getParam('id')) {
            $account = $this->accountRepository->find((int)$id);
        }

        if ($account === null) {
            $this->messageManager->addError(__('Account does not exist.'));

            return $this->_redirect('*/kaufland_account');
        }

        $this->addLicenseMessage($account);

        $headerText = __('Edit Account');
        $headerText .= ' "' . \M2E\Core\Helper\Data::escapeHtml($account->getTitle()) . '"';

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend($headerText);

        /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Account\Edit\Tabs $tabsBlock */
        $tabsBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Kaufland\Account\Edit\Tabs::class,
                '',
                [
                    'account' => $account,
                ],
            );
        $this->addLeft($tabsBlock);

        /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Account\Edit $contentBlock */
        $contentBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Account\Edit::class);

        $this->addContent($contentBlock);

        return $this->getResultPage();
    }

    private function addLicenseMessage(\M2E\Kaufland\Model\Account $account): void
    {
        try {
            $command = new \M2E\Kaufland\Model\Kaufland\Connector\Account\Get\InfoCommand(
                $account->getServerHash(),
            );
            /** @var \M2E\Kaufland\Model\Kaufland\Connector\Account\Get\Status $status */
            $status = $this->serverClient->process($command);
        } catch (\Throwable $e) {
            return;
        }

        if ($status->isActive()) {
            return;
        }

        $this->addExtendedErrorMessage(
            __(
                'Work with this Account is currently unavailable for the following reason: <br/> %error_message',
                ['error_message' => $status->getNote()],
            ),
        );
    }
}
