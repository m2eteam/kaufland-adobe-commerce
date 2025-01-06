<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Category;

class GetChildCategories extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractCategory
{
    private \M2E\Kaufland\Model\Category\Tree\Manager $categoryTreeManager;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontIdRepository;

    public function __construct(
        \M2E\Kaufland\Model\Category\Tree\Manager $categoryTreeManager,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontIdRepository
    ) {
        parent::__construct();

        $this->categoryTreeManager = $categoryTreeManager;
        $this->storefrontIdRepository = $storefrontIdRepository;
    }

    public function execute()
    {
        $storefrontId = (int)$this->getRequest()->getParam('storefront_id');
        $parentCategoryId = (int)$this->getRequest()->getParam('parent_category_id');
        $parentCategoryId = !empty($parentCategoryId) ? $parentCategoryId : null;

        $storefrontId = $this->storefrontIdRepository->find($storefrontId);
        if ($storefrontId === null) {
            $this->setJsonContent(
                [
                    'success' => false,
                    'messages' => [
                        ['error' => 'Invalid storefrontId id'],
                    ],
                ]
            );

            return $this->getResult();
        }

        $categories = $this->categoryTreeManager->getCategories($storefrontId, $parentCategoryId);

        $response = [];

        foreach ($categories as $category) {
            $isLeaf = count($this->categoryTreeManager->getCategories($storefrontId, $category->getCategoryId())) === 0;

            $response[] = [
                'category_id' => $category->getCategoryId(),
                'title' => $category->getTitle(),
                'is_leaf' => $isLeaf,
            ];
        }

        $this->setJsonContent($response);

        return $this->getResult();
    }
}
