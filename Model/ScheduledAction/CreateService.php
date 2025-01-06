<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ScheduledAction;

class CreateService
{
    private \M2E\Kaufland\Model\ScheduledActionFactory $scheduledActionFactory;
    /** @var \M2E\Kaufland\Model\ScheduledAction\Repository */
    private Repository $repository;

    public function __construct(
        \M2E\Kaufland\Model\ScheduledActionFactory $scheduledActionFactory,
        Repository $repository
    ) {
        $this->scheduledActionFactory = $scheduledActionFactory;
        $this->repository = $repository;
    }

    public function create(
        \M2E\Kaufland\Model\Product $listingProduct,
        int $action,
        array $data,
        array $tags = [],
        bool $isForce = false,
        ?\M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator $configurator = null
    ): \M2E\Kaufland\Model\ScheduledAction {
        $scheduledAction = $this->repository->findByListingProductIdAndType($listingProduct->getId(), $action);
        if ($scheduledAction === null) {
            $scheduledAction = $this->scheduledActionFactory->create();
        }

        $scheduledAction->init(
            $listingProduct,
            $action,
            $data,
            $isForce,
            $tags,
            $configurator,
        );

        $this->repository->create($scheduledAction);

        return $scheduledAction;
    }
}
