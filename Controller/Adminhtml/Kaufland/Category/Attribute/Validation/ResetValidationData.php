<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Category\Attribute\Validation;

class ResetValidationData extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractCategory
{
    private \M2E\Kaufland\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $categoryId = (int)$this->getRequest()->getParam('template_category_id');
        $this->productRepository->resetCategoryAttributesValidationData($categoryId);

        return $this->getResult();
    }
}
