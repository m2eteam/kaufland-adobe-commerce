<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Category;

class DeleteService
{
    private \M2E\Kaufland\Model\Listing\Auto\Category\Repository $autoCategoryRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Auto\Category\Repository $autoCategoryRepository
    ) {
        $this->autoCategoryRepository = $autoCategoryRepository;
    }

    public function execute(\M2E\Kaufland\Model\Listing\Auto\Category $category): void
    {
        $this->autoCategoryRepository->delete($category);
    }
}
