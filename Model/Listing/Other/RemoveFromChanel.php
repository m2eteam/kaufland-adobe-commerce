<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Other;

class RemoveFromChanel
{
    private \M2E\Kaufland\Model\Listing\Other\DeleteService $unmanagedDelete;
    private \M2E\Kaufland\Model\StopQueue\CreateService $stopQueueCreateService;

    public function __construct(
        DeleteService $unmanagedDelete,
        \M2E\Kaufland\Model\StopQueue\CreateService $stopQueueCreateService
    ) {
        $this->unmanagedDelete = $unmanagedDelete;
        $this->stopQueueCreateService = $stopQueueCreateService;
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Other[] $products
     */
    public function execute(array $products): void
    {
        foreach ($products as $product) {
            $this->stopQueueCreateService->createFromUnmanagedProduct($product);
            $this->unmanagedDelete->process($product);
        }
    }
}
