<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\Synchronization;

class DeleteService extends \M2E\Kaufland\Model\Template\AbstractDeleteService
{
    private \M2E\Kaufland\Model\Template\Synchronization\Repository $synchronizationRepository;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Template\Synchronization\Repository $synchronizationRepository,
        \M2E\Kaufland\Model\Listing\Repository $listingRepository
    ) {
        $this->synchronizationRepository = $synchronizationRepository;
        $this->listingRepository = $listingRepository;
    }

    protected function loadPolicy(int $id): \M2E\Kaufland\Model\Template\PolicyInterface
    {
        return $this->synchronizationRepository->get($id);
    }

    protected function isUsedPolicy(\M2E\Kaufland\Model\Template\PolicyInterface $policy): bool
    {
        return $this->listingRepository->isExistListingBySyncPolicy($policy->getId());
    }

    protected function delete(\M2E\Kaufland\Model\Template\PolicyInterface $policy): void
    {
        /** @var \M2E\Kaufland\Model\Template\Synchronization $policy */
        $this->synchronizationRepository->delete($policy);
    }
}
