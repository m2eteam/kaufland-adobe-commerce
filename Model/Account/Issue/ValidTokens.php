<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Account\Issue;

use M2E\Kaufland\Model\Issue\DataObject as Issue;

class ValidTokens implements \M2E\Kaufland\Model\Issue\LocatorInterface
{
    public const ACCOUNT_TOKENS_CACHE_KEY = 'kaufland_account_tokens_validations';

    private \M2E\Kaufland\Helper\View\Kaufland $viewHelper;
    private \M2E\Kaufland\Helper\Data\Cache\Permanent $permanentCacheHelper;
    private \M2E\Kaufland\Model\Issue\DataObjectFactory $issueFactory;
    private \M2E\Kaufland\Model\Connector\Client\Single $connector;
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Kaufland\Helper\View\Kaufland $viewHelper,
        \M2E\Kaufland\Helper\Data\Cache\Permanent $permanentCacheHelper,
        \M2E\Kaufland\Model\Issue\DataObjectFactory $issueFactory,
        \M2E\Kaufland\Model\Connector\Client\Single $connector,
        \M2E\Kaufland\Model\Account\Repository $accountRepository
    ) {
        $this->viewHelper = $viewHelper;
        $this->permanentCacheHelper = $permanentCacheHelper;
        $this->issueFactory = $issueFactory;
        $this->connector = $connector;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @inheritDoc
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \M2E\Kaufland\Model\Exception
     * @throws \Exception
     */
    public function getIssues(): array
    {
        if (!$this->isNeedProcess()) {
            return [];
        }

        $accounts = $this->permanentCacheHelper->getValue(self::ACCOUNT_TOKENS_CACHE_KEY);
        if ($accounts !== null) {
            return $this->prepareIssues($accounts);
        }

        try {
            $accounts = $this->retrieveNotValidAccounts();
        } catch (\M2E\Core\Model\Exception $e) {
            $accounts = [];
        }

        $this->permanentCacheHelper->setValue(
            self::ACCOUNT_TOKENS_CACHE_KEY,
            $accounts,
            ['account'],
            3600,
        );

        return $this->prepareIssues($accounts);
    }

    /**
     * @return array
     * @throws \M2E\Kaufland\Model\Exception
     */
    private function retrieveNotValidAccounts(): array
    {
        $accountsHashes = $this->getPreparedAccountsData();
        if (empty($accountsHashes)) {
            return [];
        }

        $command = new \M2E\Kaufland\Model\Kaufland\Connector\Account\Get\AuthInfoCommand(
            array_keys($accountsHashes),
        );
        /** @var \M2E\Kaufland\Model\Kaufland\Connector\Account\Get\Result $validateResult */
        $validateResult = $this->connector->process($command);
        $result = [];
        foreach ($accountsHashes as $hash => $title) {
            if (!$validateResult->isValidAccount($hash)) {
                $result[]['account_name'] = $title;
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function isNeedProcess(): bool
    {
        return $this->viewHelper->isInstallationWizardFinished();
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function prepareIssues(array $data): array
    {
        $issues = [];
        foreach ($data as $account) {
            $issues[] = $this->getIssue($account['account_name']);
        }

        return $issues;
    }

    private function getIssue(string $accountName): Issue
    {
        $text = __(
            "The token of %channel_title account \"%account_name\" is no longer valid.
         Please edit your %channel_title account and get a new token.",
            [
                'account_name' => $accountName,
                'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
            ],
        );

        return $this->issueFactory->createErrorDataObject($accountName, (string)$text, null);
    }

    private function getPreparedAccountsData(): array
    {
        $accountsHashes = [];
        foreach ($this->accountRepository->getAll() as $account) {
            $accountsHashes[$account->getServerHash()] = $account->getTitle();
        }

        return $accountsHashes;
    }
}
