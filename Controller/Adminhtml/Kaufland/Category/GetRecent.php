<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Category;

class GetRecent extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractCategory
{
    /** @var \M2E\Kaufland\Model\Category\Dictionary\Repository */
    private $categoryRepository;

    public function __construct(
        \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryRepository
    ) {
        parent::__construct();
        $this->categoryRepository = $categoryRepository;
    }

    public function execute()
    {
        $storefrontId = $this->getRequest()->getParam('storefront_id');
        $categories = $this->categoryRepository->getByStorefrontId($storefrontId);

        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->getCategoryId(),
                'path' => $category->getPathWithCategoryId(),
            ];
        }

        $this->setJsonContent($result);

        return $this->getResult();
    }
}
