<?php

namespace M2E\Kaufland\Observer;

class Category extends \M2E\Kaufland\Observer\AbstractObserver
{
    private \M2E\Kaufland\Model\Listing\Auto\Actions\CategoryProductRelationProcessor $categoryProductRelationshipsProcessor;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Auto\Actions\CategoryProductRelationProcessor  $categoryProductRelationshipsProcessor,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory
    ) {
        parent::__construct($activeRecordFactory, $modelFactory);
        $this->categoryProductRelationshipsProcessor = $categoryProductRelationshipsProcessor;
    }

    /**
     * @see \Magento\Catalog\Model\ResourceModel\Category::_saveCategoryProducts()
     */
    public function process(): void
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->getEventObserver()->getData('category');

        /**
         * For insert\update
         * @var int[] $changedProductsIds
         */
        $changedProductsIds = $category->getChangedProductIds();
        if (empty($changedProductsIds)) {
            return;
        }

        $categoryId = (int)$category->getId();
        if (!$this->categoryProductRelationshipsProcessor->isNeedProcess($categoryId)) {
            return;
        }

        /**
         * Product IDs from new category-product relationships
         * @var int[] $postedProductsIds
         * @psalm-suppress UndefinedMagicMethod
         */
        $postedProductsIds = array_keys($category->getPostedProducts());
        $websiteId = (int)$category->getStore()->getWebsiteId();

        $this->categoryProductRelationshipsProcessor->process(
            $categoryId,
            $websiteId,
            $postedProductsIds,
            $changedProductsIds
        );
    }
}
