<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction;

class IsCategoryGroupTitleUnique extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction
{
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->autoCategoryGroupRepository = $autoCategoryGroupRepository;
    }

    public function execute()
    {
        $title = $this->getTitleFromRequest();
        if (empty($title)) {
            $this->setJsonContent(['unique' => false]);

            return $this->getResult();
        }

        $isTitleUnique = $this->autoCategoryGroupRepository
            ->isTitleUnique(
                $title,
                $this->getListingIdFromRequest(),
                $this->getGroupIdFromRequest()
            );

        $this->setJsonContent(['unique' => $isTitleUnique]);

        return $this->getResult();
    }

    private function getTitleFromRequest(): ?string
    {
        $title = $this->getRequest()->getParam('title');
        if (empty($title)) {
            return null;
        }

        return (string)$title;
    }

    private function getListingIdFromRequest(): int
    {
        return (int)$this->getRequest()->getParam('id');
    }

    private function getGroupIdFromRequest(): ?int
    {
        $groupId = $this->getRequest()->getParam('group_id');
        if (empty($groupId)) {
            return null;
        }

        return (int)$groupId;
    }
}
