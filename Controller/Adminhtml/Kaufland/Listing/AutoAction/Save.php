<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction;

use M2E\Kaufland\Model\Listing as Listing;

class Save extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction
{
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;
    private \M2E\Kaufland\Model\Listing\Auto\Category\GroupFactory $categoryGroupFactory;
    private \M2E\Kaufland\Model\Listing\Auto\CategoryFactory $categoryFactory;
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $categoryGroupRepository;
    private \M2E\Kaufland\Model\Listing\Auto\Category\Repository $categoryRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Model\Listing\Auto\Category\GroupFactory $categoryGroupFactory,
        \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $categoryGroupRepository,
        \M2E\Kaufland\Model\Listing\Auto\CategoryFactory $categoryFactory,
        \M2E\Kaufland\Model\Listing\Auto\Category\Repository $categoryRepository,
        \M2E\Kaufland\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->listingRepository = $listingRepository;
        $this->categoryGroupFactory = $categoryGroupFactory;
        $this->categoryFactory = $categoryFactory;
        $this->categoryGroupRepository = $categoryGroupRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \M2E\Core\Model\Exception\Logic
     */
    public function execute()
    {
        $requestData = \M2E\Core\Helper\Json::decode(
            $this->getRequest()->getPost('auto_action_data')
        );

        if ($requestData === null) {
            $this->setJsonContent(['success' => false]);

            return $this->getResult();
        }

        $listing = $this->listingRepository
            ->get((int)$this->getRequest()->getParam('id'));

        $this->saveListing($requestData, $listing);

        $this->setJsonContent(['success' => true]);

        return $this->getResult();
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function saveListing($requestData, Listing $listing)
    {
        if ($requestData['auto_mode'] == Listing::AUTO_MODE_GLOBAL) {
            $listing->setGlobalAutoAction(
                (int)$requestData['auto_global_adding_mode'],
                (int)$requestData['auto_global_adding_add_not_visible'],
                (int)($requestData['template_category_data']['value'] ?? 0)
            );
        }

        if ($requestData['auto_mode'] == Listing::AUTO_MODE_WEBSITE) {
            $listing->setWebsiteAutoAction(
                (int)$requestData['auto_website_adding_mode'],
                (int)$requestData['auto_website_deleting_mode'],
                (int)$requestData['auto_website_adding_add_not_visible'],
                (int)($requestData['template_category_data']['value'] ?? 0)
            );
        }

        if ($requestData['auto_mode'] == Listing::AUTO_MODE_CATEGORY) {
            $listing->setCategoryAutoAction();
            $this->saveAutoCategory($requestData, $listing->getId());
        }

        $this->listingRepository->save($listing);
    }

    private function saveAutoCategory(array $requestData, int $listingId)
    {
        $categoryGroup = $this->createOrUpdateCategoryGroup($requestData, $listingId);
        $this->categoryRepository->deleteByCategoryGroupId($categoryGroup->getId());
        foreach ($requestData['categories'] as $categoryId) {
            $category = $this->categoryFactory->create();
            $category->init($categoryGroup->getId(), (int)$categoryId);
            $this->categoryRepository->create($category);
        }
    }

    private function createOrUpdateCategoryGroup(array $requestData, int $listingId): Listing\Auto\Category\Group
    {
        $categoryGroup = $this->categoryGroupFactory->create();
        if ($requestData['id'] > 0) {
            $categoryGroup = $this->categoryGroupRepository->get((int)$requestData['id']);
        }

        $categoryGroup->init(
            (string)($requestData['title'] ?? ''),
            $listingId,
            (int)$requestData['adding_mode'],
            (int)$requestData['adding_add_not_visible'],
            (int)$requestData['deleting_mode'],
            (int)$requestData['template_category_data']['value']
        );

        if ($categoryGroup->isObjectNew()) {
            $this->categoryGroupRepository->create($categoryGroup);
        } else {
            $this->categoryGroupRepository->save($categoryGroup);
        }

        return $categoryGroup;
    }
}
