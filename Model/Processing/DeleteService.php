<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Processing;

class DeleteService
{
    private \M2E\Kaufland\Model\Processing\Repository $processingRepository;
    private \M2E\Kaufland\Model\Processing\Lock\Repository $processingLockRepository;

    public function __construct(
        \M2E\Kaufland\Model\Processing\Repository $processingRepository,
        \M2E\Kaufland\Model\Processing\Lock\Repository $processingLockRepository
    ) {
        $this->processingRepository = $processingRepository;
        $this->processingLockRepository = $processingLockRepository;
    }

    public function deleteByObjAndObjId(string $objName, int $objId): void
    {
        $processingIds = [];
        foreach ($this->processingLockRepository->findByObjNameAndObjId($objName, $objId) as $lock) {
            $processingIds[] = $lock->getProcessingId();
        }

        foreach ($this->processingRepository->findByIds($processingIds) as $processing) {
            $this->processingRepository->forceRemove($processing);
        }
    }
}
