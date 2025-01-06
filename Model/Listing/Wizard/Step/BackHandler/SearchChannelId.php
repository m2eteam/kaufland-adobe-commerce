<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Wizard\Step\BackHandler;

class SearchChannelId implements \M2E\Kaufland\Model\Listing\Wizard\Step\BackHandlerInterface
{
    private \M2E\Kaufland\Model\Listing\Wizard\Repository $repository;

    public function __construct(\M2E\Kaufland\Model\Listing\Wizard\Repository $repository)
    {
        $this->repository = $repository;
    }

    public function process(\M2E\Kaufland\Model\Listing\Wizard\Manager $manager): void
    {
        $this->repository->resetSearchChannelIdForAllProducts($manager->getWizard());
    }
}
