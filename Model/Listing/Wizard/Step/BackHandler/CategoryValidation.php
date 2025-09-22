<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Wizard\Step\BackHandler;

class CategoryValidation implements \M2E\Kaufland\Model\Listing\Wizard\Step\BackHandlerInterface
{
    public function process(\M2E\Kaufland\Model\Listing\Wizard\Manager $manager): void
    {
        $manager->resetCategoryValidationData();
    }
}
