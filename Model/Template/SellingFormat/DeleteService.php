<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\SellingFormat;

class DeleteService extends \M2E\Kaufland\Model\Template\AbstractDeleteService
{
    private \M2E\Kaufland\Model\Template\SellingFormat\Repository $sellingFormatRepository;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Template\SellingFormat\Repository $sellingFormatRepository,
        \M2E\Kaufland\Model\Listing\Repository $listingRepository
    ) {
        $this->sellingFormatRepository = $sellingFormatRepository;
        $this->listingRepository = $listingRepository;
    }

    protected function loadPolicy(int $id): \M2E\Kaufland\Model\Template\PolicyInterface
    {
        return $this->sellingFormatRepository->get($id);
    }

    protected function isUsedPolicy(\M2E\Kaufland\Model\Template\PolicyInterface $policy): bool
    {
        return $this->listingRepository->isExistListingBySellingPolicy($policy->getId());
    }

    protected function delete(\M2E\Kaufland\Model\Template\PolicyInterface $policy): void
    {
        /** @var \M2E\Kaufland\Model\Template\SellingFormat $policy */
        $this->sellingFormatRepository->delete($policy);
    }
}
