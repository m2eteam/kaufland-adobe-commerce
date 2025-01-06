<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\InventorySync;

class AccountLockManager
{
    private const PREFIX_LOCK_NICK = 'synchronization_listing_inventory_for_storefront_';
    private const LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME = 3600; // 60 min

    private \M2E\Kaufland\Model\Lock\Item\ManagerFactory $lockItemManagerFactory;

    public function __construct(
        \M2E\Kaufland\Model\Lock\Item\ManagerFactory $lockItemManagerFactory
    ) {
        $this->lockItemManagerFactory = $lockItemManagerFactory;
    }

    public function isExistByStorefront(\M2E\Kaufland\Model\Storefront $storefront): bool
    {
        $lockManager = $this->getLockManager($storefront);

        if ($lockManager->isExist() === false) {
            return false;
        }

        if ($lockManager->isInactiveMoreThanSeconds(self::LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME)) {
            $lockManager->remove();

            return false;
        }

        return true;
    }

    public function isExistByAccount(\M2E\Kaufland\Model\Account $account): bool
    {
        foreach ($account->getStorefronts() as $storefront) {
            if ($this->isExistByStorefront($storefront)) {
                return true;
            }
        }

        return false;
    }

    public function create(\M2E\Kaufland\Model\Storefront $storefront): void
    {
        $lockManager = $this->getLockManager($storefront);
        $lockManager->create();
    }

    public function remove(\M2E\Kaufland\Model\Storefront $storefront): void
    {
        $lockManager = $this->getLockManager($storefront);
        $lockManager->remove();
    }

    private function getLockManager(\M2E\Kaufland\Model\Storefront $storefront): \M2E\Kaufland\Model\Lock\Item\Manager
    {
        return $this->lockItemManagerFactory->create($this->makeLockNick($storefront->getId()));
    }

    private function makeLockNick(int $storefrontId): string
    {
        return self::PREFIX_LOCK_NICK . $storefrontId;
    }
}
