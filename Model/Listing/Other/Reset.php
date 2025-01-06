<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Other;

class Reset
{
    private Repository $repository;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;

    public function __construct(
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        Repository $listingOtherRepository
    ) {
        $this->repository = $listingOtherRepository;
        $this->storefrontRepository = $storefrontRepository;
    }

    public function process(\M2E\Kaufland\Model\Account $account): void
    {
        $this->removeListingOther($account);
        $this->resetStorefrontInventoryLastSyncDate($account);
    }

    private function removeListingOther(\M2E\Kaufland\Model\Account $account): void
    {
        $this->repository->removeByAccountId($account->getId());
    }

    private function resetStorefrontInventoryLastSyncDate(\M2E\Kaufland\Model\Account $account): void
    {
        foreach ($account->getStorefronts() as $storefront) {
            $storefront->resetInventoryLastSyncDate();
            $this->storefrontRepository->save($storefront);
        }
    }
}
