<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\InventorySync\Processing;

class Initiator implements \M2E\Kaufland\Model\Processing\PartialInitiatorInterface
{
    private \M2E\Kaufland\Model\Account $account;
    private \M2E\Kaufland\Model\Storefront $storefront;
    private \M2E\Kaufland\Model\Listing\InventorySync\AccountLockManager $accountLockManager;

    public function __construct(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront,
        \M2E\Kaufland\Model\Listing\InventorySync\AccountLockManager $accountLockManager
    ) {
        $this->account = $account;
        $this->storefront = $storefront;
        $this->accountLockManager = $accountLockManager;
    }

    public function getInitCommand(): \M2E\Core\Model\Connector\CommandProcessingInterface
    {
        return new Connector\InventoryGetItemsCommand(
            $this->account->getServerHash(),
            $this->storefront->getStorefrontCode(),
        );
    }

    public function generateProcessParams(): array
    {
        return [
            'account_id' => $this->account->getId(),
            'storefront' => $this->storefront->getStorefrontCode(),
            'current_date' => \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'),
        ];
    }

    public function getResultHandlerNick(): string
    {
        return ResultHandler::NICK;
    }

    public function initLock(\M2E\Kaufland\Model\Processing\LockManager $lockManager): void
    {
        $lockManager->create(\M2E\Kaufland\Model\Storefront::LOCK_NICK, $this->storefront->getId());
        $this->accountLockManager->create($this->storefront);
    }
}
