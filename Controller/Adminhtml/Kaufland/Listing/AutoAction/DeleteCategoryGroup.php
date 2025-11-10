<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction;

class DeleteCategoryGroup extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction
{
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository;
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group\DeleteService $autoCategoryGroupDeleteService;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository,
        \M2E\Kaufland\Model\Listing\Auto\Category\Group\DeleteService $autoCategoryGroupDeleteService,
        $context = null
    ) {
        parent::__construct($context);
        $this->autoCategoryGroupRepository = $autoCategoryGroupRepository;
        $this->autoCategoryGroupDeleteService = $autoCategoryGroupDeleteService;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function execute()
    {
        $categoryGroup = $this->autoCategoryGroupRepository
            ->get((int)$this->getRequest()->getParam('group_id'));

        $this->autoCategoryGroupDeleteService->execute($categoryGroup);
    }
}
