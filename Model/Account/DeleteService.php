<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Account;

use M2E\Kaufland\Model\Account\Issue\ValidTokens;
use M2E\Kaufland\Model\Account\Repository;

class DeleteService
{
    private Repository $accountRepository;
    private \M2E\Kaufland\Model\Order\Repository $orderRepository;
    private \M2E\Kaufland\Model\Order\Log\Repository $orderLogRepository;
    private \M2E\Kaufland\Model\Listing\Log\Repository $listingLogRepository;
    private \M2E\Kaufland\Helper\Module\Exception $exceptionHelper;
    private \M2E\Kaufland\Helper\Data\Cache\Permanent $cache;
    private \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository;
    private \M2E\Kaufland\Model\Listing\DeleteService $listingDeleteService;
    private \M2E\Kaufland\Model\Processing\DeleteService $processingDeleteService;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;
    private \M2E\Kaufland\Model\Warehouse\Repository $warehouseRepository;
    private \M2E\Kaufland\Model\ShippingGroup\Repository $shippingGroupRepository;

    public function __construct(
        Repository $accountRepository,
        \M2E\Kaufland\Model\Processing\DeleteService $processingDeleteService,
        \M2E\Kaufland\Model\Listing\DeleteService $listingDeleteService,
        \M2E\Kaufland\Model\Order\Repository $orderRepository,
        \M2E\Kaufland\Model\Order\Log\Repository $orderLogRepository,
        \M2E\Kaufland\Helper\Module\Exception $exceptionHelper,
        \M2E\Kaufland\Model\Listing\Log\Repository $listingLogRepository,
        \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository,
        \M2E\Kaufland\Helper\Data\Cache\Permanent $cache,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        \M2E\Kaufland\Model\Warehouse\Repository $warehouseRepository,
        \M2E\Kaufland\Model\ShippingGroup\Repository $shippingGroupRepository
    ) {
        $this->accountRepository = $accountRepository;
        $this->listingLogRepository = $listingLogRepository;
        $this->exceptionHelper = $exceptionHelper;
        $this->cache = $cache;
        $this->listingOtherRepository = $listingOtherRepository;
        $this->listingDeleteService = $listingDeleteService;
        $this->processingDeleteService = $processingDeleteService;
        $this->storefrontRepository = $storefrontRepository;
        $this->orderRepository = $orderRepository;
        $this->orderLogRepository = $orderLogRepository;
        $this->warehouseRepository = $warehouseRepository;
        $this->shippingGroupRepository = $shippingGroupRepository;
    }

    /**
     * @param \M2E\Kaufland\Model\Account $account
     *
     * @return void
     * @throws \Throwable
     */
    public function delete(\M2E\Kaufland\Model\Account $account): void
    {
        $accountId = $account->getId();

        try {
            $this->orderLogRepository->removeByAccountId($accountId);

            $this->orderRepository->removeByAccountId($accountId);

            $this->listingLogRepository->removeByAccountId($accountId);

            $this->listingOtherRepository->removeByAccountId($accountId);

            $this->removeListings($account);

            $this->deleteStorefront($account);
            $this->warehouseRepository->removeByAccountId($accountId);
            $this->shippingGroupRepository->removeByAccountId($accountId);

            $this->deleteAccount($account);
        } catch (\Throwable $e) {
            $this->exceptionHelper->process($e);
            throw $e;
        }
    }

    private function removeListings(\M2E\Kaufland\Model\Account $account): void
    {
        foreach ($account->getListings() as $listing) {
            $this->listingDeleteService->process($listing, true);
        }
    }

    private function deleteStorefront(\M2E\Kaufland\Model\Account $account): void
    {
        foreach ($account->getStorefronts() as $storefront) {
            $this->processingDeleteService->deleteByObjAndObjId(
                \M2E\Kaufland\Model\Storefront::LOCK_NICK,
                $storefront->getId()
            );

            $this->storefrontRepository->remove($storefront);
        }
    }

    private function deleteAccount(\M2E\Kaufland\Model\Account $account): void
    {
        $this->cache->removeTagValues('account');

        $this->accountRepository->remove($account);

        $this->cache->removeValue(ValidTokens::ACCOUNT_TOKENS_CACHE_KEY);
    }
}
