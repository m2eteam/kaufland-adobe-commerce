<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Other;

class DeleteService
{
    /** @var \M2E\Kaufland\Model\Listing\Other\Repository */
    private Repository $repository;
    /**
     * @var \M2E\Kaufland\Model\Listing\Other\MappingService
     */
    private MappingService $mappingService;

    public function __construct(
        Repository $repository,
        MappingService $mappingService
    ) {
        $this->repository = $repository;
        $this->mappingService = $mappingService;
    }

    public function process(\M2E\Kaufland\Model\Listing\Other $other): void
    {
        $this->repository->remove($other);
    }

    public function byMagentoProductId(int $magentoProductId): void
    {
        $this->repository->findByMagentoProductId($magentoProductId);

        foreach ($this->repository->findByMagentoProductId($magentoProductId) as $listingOther) {
            $this->mappingService->unMap($listingOther);
        }
    }
}
