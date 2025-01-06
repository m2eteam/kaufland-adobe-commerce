<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Category;

class GetSelectedCategoryDetails extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractCategory
{
    private \M2E\Kaufland\Model\Category\Tree\Repository $treeRepository;
    private \M2E\Kaufland\Model\Category\Tree\PathBuilder $pathBuilder;

    public function __construct(
        \M2E\Kaufland\Model\Category\Tree\Repository $treeRepository,
        \M2E\Kaufland\Model\Category\Tree\PathBuilder $pathBuilder
    ) {
        parent::__construct();
        $this->treeRepository = $treeRepository;
        $this->pathBuilder = $pathBuilder;
    }

    public function execute()
    {
        $storefrontId = (int)$this->getRequest()->getParam('storefront_id');
        $categoryId = (int)$this->getRequest()->getParam('value');

        if (
            empty($storefrontId)
            || empty($categoryId)
        ) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Invalid input');
        }

        $category = $this->treeRepository->getCategoryByStorefrontIdAndCategoryId($storefrontId, $categoryId);
        if ($category === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Category invalid');
        }

        $path = $this->pathBuilder->getPath($category);
        $details = [
            'path' => $path,
            'interface_path' => sprintf('%s (%s)', $path, $categoryId),
            'template_id' => null,
            'is_custom_template' => null,
        ];

        $this->setJsonContent($details);

        return $this->getResult();
    }
}
