<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction;

class DeleteCategory extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction
{
    private \M2E\Kaufland\Model\Listing\Auto\Category\Repository $autoCategoryRepository;
    private \M2E\Kaufland\Model\Listing\Auto\Category\DeleteService $autoCategoryDeleteService;
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group\DeleteService $autoCategoryGroupDeleteService;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Auto\Category\Repository $autoCategoryRepository,
        \M2E\Kaufland\Model\Listing\Auto\Category\DeleteService $autoCategoryDeleteService,
        \M2E\Kaufland\Model\Listing\Auto\Category\Group\DeleteService $autoCategoryGroupDeleteService,
        $context = null
    ) {
        parent::__construct($context);
        $this->autoCategoryRepository = $autoCategoryRepository;
        $this->autoCategoryDeleteService = $autoCategoryDeleteService;
        $this->autoCategoryGroupDeleteService = $autoCategoryGroupDeleteService;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function execute()
    {
        $this->deleteCategoryFromGroup(
            $this->getGroupIdFromRequest(),
            $this->getCategoryIdFromRequest()
        );
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function getGroupIdFromRequest(): int
    {
        $groupId = $this->getRequest()->getParam('group_id');
        if (empty($groupId)) {
            throw new \M2E\Kaufland\Model\Exception\Logic("Missing required parameter: 'group_id'.");
        }

        return (int)$groupId;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function getCategoryIdFromRequest(): int
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        if (empty($categoryId)) {
            throw new \M2E\Kaufland\Model\Exception\Logic("Missing required parameter: 'category_id'.");
        }

        return (int)$categoryId;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function deleteCategoryFromGroup(int $groupId, int $categoryId): void
    {
        $categories = $this->autoCategoryRepository
            ->getByGroupIdAndCategoryId($groupId, $categoryId);

        foreach ($categories as $category) {
            $this->autoCategoryDeleteService->execute($category);
            $categoryGroup = $category->getCategoryGroup();
            if (!$categoryGroup->hasCategories()) {
                $this->autoCategoryGroupDeleteService->execute($categoryGroup);
            }
        }
    }
}
