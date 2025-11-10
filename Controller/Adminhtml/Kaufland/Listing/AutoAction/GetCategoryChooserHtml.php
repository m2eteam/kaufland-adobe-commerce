<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction;

class GetCategoryChooserHtml extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction
{
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository,
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->autoCategoryGroupRepository = $autoCategoryGroupRepository;
        $this->listingRepository = $listingRepository;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function execute()
    {
        /** @var \M2E\Kaufland\Block\Adminhtml\Category\CategoryChooser $categoryChooserBlock */
        $categoryChooserBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Category\CategoryChooser::class,
                '',
                ['selectedCategory' => $this->getSelectedCategoryId()]
            );

        $this->setAjaxContent($categoryChooserBlock);

        return $this->getResult();
    }

    private function getGroupIdFromRequest(): ?int
    {
        $groupId = $this->getRequest()->getParam('group_id');
        if (empty($groupId)) {
            return null;
        }

        return (int)$groupId;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function getSelectedCategoryId(): ?int
    {
        $listing = $this->listingRepository
            ->get((int)$this->getRequest()->getParam('id'));

        if ($listing->isAutoModeGlobal()) {
            return $listing->getAutoGlobalAddingTemplateCategoryId();
        }

        if ($listing->isAutoModeWebsite()) {
            return $listing->getAutoWebsiteAddingTemplateCategoryId();
        }

        $groupId = $this->getGroupIdFromRequest();
        if ($groupId === null) {
            return null;
        }

        $categoryGroup = $this->autoCategoryGroupRepository->get($groupId);

        return $categoryGroup->getAddingTemplateCategoryId();
    }
}
