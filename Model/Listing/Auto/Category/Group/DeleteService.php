<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Category\Group;

class DeleteService
{
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository;
    private \M2E\Kaufland\Model\Listing\Auto\Category\DeleteService $autoCategoryDeleteService;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository,
        \M2E\Kaufland\Model\Listing\Auto\Category\DeleteService $autoCategoryDeleteService
    ) {
        $this->autoCategoryGroupRepository = $autoCategoryGroupRepository;
        $this->autoCategoryDeleteService = $autoCategoryDeleteService;
    }

    public function execute(\M2E\Kaufland\Model\Listing\Auto\Category\Group $categoryGroup)
    {
        foreach ($categoryGroup->getCategories() as $category) {
            $this->autoCategoryDeleteService->execute($category);
        }

        $this->autoCategoryGroupRepository->delete($categoryGroup);
    }
}
