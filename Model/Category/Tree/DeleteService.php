<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Tree;

class DeleteService
{
    private \M2E\Kaufland\Model\Category\Tree\Repository $categoryTreeRepository;
    private \M2E\Kaufland\Model\Registry\Manager $registry;

    public function __construct(
        \M2E\Kaufland\Model\Category\Tree\Repository $categoryTreeRepository,
        \M2E\Kaufland\Model\Registry\Manager $registry
    ) {
        $this->categoryTreeRepository = $categoryTreeRepository;
        $this->registry = $registry;
    }

    public function deleteByStorefront(\M2E\Kaufland\Model\Storefront $storefront): void
    {
        $key = \M2E\Kaufland\Model\Category\Tree\Manager::prepareRegistryKey($storefront);
        $this->registry->deleteValue($key);

        $this->categoryTreeRepository->deleteByStorefrontId($storefront->getId());
    }
}
