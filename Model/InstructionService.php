<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model;

use M2E\Kaufland\Model\Instruction;

class InstructionService
{
    private \M2E\Kaufland\Model\Instruction\Repository $repository;

    public function __construct(
        Instruction\Repository $repository
    ) {
        $this->repository = $repository;
    }

    public function create(
        int $listingProductId,
        string $type,
        string $initiator,
        int $priority,
        ?\DateTime $skipUntil = null
    ): void {
        $this->createBatch(
            [
                [
                    'listing_product_id' => $listingProductId,
                    'type' => $type,
                    'initiator' => $initiator,
                    'priority' => $priority,
                    'skip_until' => $skipUntil,
                ],
            ],
        );
    }

    /**
     * @param list<array{listing_product_id:int, type: string, initiator: int, priority: int, skip_until?:\DateTime}>
     *     $data
     *
     * @return void
     */
    public function createBatch(array $data): void
    {
        $this->repository->createMultiple($data);
    }
}
