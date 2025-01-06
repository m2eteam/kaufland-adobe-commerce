<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\Shipping;

class DeleteService extends \M2E\Kaufland\Model\Template\AbstractDeleteService
{
    private \M2E\Kaufland\Model\Template\Shipping\Repository $shippingRepository;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Template\Shipping\Repository $shippingRepository,
        \M2E\Kaufland\Model\Listing\Repository $listingRepository
    ) {
        $this->shippingRepository = $shippingRepository;
        $this->listingRepository = $listingRepository;
    }

    protected function loadPolicy(int $id): \M2E\Kaufland\Model\Template\PolicyInterface
    {
        return $this->shippingRepository->get($id);
    }

    protected function isUsedPolicy(\M2E\Kaufland\Model\Template\PolicyInterface $policy): bool
    {
        return $this->listingRepository->isExistListingByShippingPolicy($policy->getId());
    }

    protected function delete(\M2E\Kaufland\Model\Template\PolicyInterface $policy): void
    {
        /** @var \M2E\Kaufland\Model\Template\Shipping $policy */
        $this->shippingRepository->delete($policy);
    }
}
