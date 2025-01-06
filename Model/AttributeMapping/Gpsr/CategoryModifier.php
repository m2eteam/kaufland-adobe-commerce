<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\AttributeMapping\Gpsr;

class CategoryModifier
{
    private const COUNT_CATEGORIES_FOR_CYCLE = 50;

    private \M2E\Kaufland\Model\ResourceModel\Category\Dictionary\CollectionFactory $templateCategoryCollectionFactory;
    private \M2E\Kaufland\Model\Kaufland\Template\Category\AffectedListingsProductsFactory $affectedListingsProductsFactory;
    private \M2E\Kaufland\Model\AttributeMapping\Gpsr\CategoryModifier\CategoryDiffStub $categoryDiffStub;
    private \M2E\Kaufland\Model\Kaufland\Template\Category\ChangeProcessorFactory $changeProcessorFactory;
    private \M2E\Kaufland\Model\Category\Attribute\Repository $attributeRepository;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Category\Dictionary\CollectionFactory $templateCategoryCollectionFactory,
        \M2E\Kaufland\Model\Kaufland\Template\Category\AffectedListingsProductsFactory $affectedListingsProductsFactory,
        \M2E\Kaufland\Model\AttributeMapping\Gpsr\CategoryModifier\CategoryDiffStub $categoryDiffStub,
        \M2E\Kaufland\Model\Kaufland\Template\Category\ChangeProcessorFactory $changeProcessorFactory,
        \M2E\Kaufland\Model\Category\Attribute\Repository $attributeRepository
    ) {
        $this->templateCategoryCollectionFactory = $templateCategoryCollectionFactory;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
        $this->categoryDiffStub = $categoryDiffStub;
        $this->changeProcessorFactory = $changeProcessorFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param \M2E\Kaufland\Model\AttributeMapping\Gpsr\Pair[] $gpsrAttributes
     *
     * @return void
     */
    public function process(array $gpsrAttributes): void
    {
        $categoryTemplateId = 0;
        do {
            $categories = $this->getCategories($categoryTemplateId);
            foreach ($categories as $category) {
                $categoryTemplateId = (int)$category->getId();

                $isChangedCategory = $this->processCategory($category, $gpsrAttributes);
                if (!$isChangedCategory) {
                    continue;
                }

                $this->createProductInstruction($category);
            }
        } while (count($categories) === self::COUNT_CATEGORIES_FOR_CYCLE);
    }

    /**
     * @param int $fromId
     *
     * @return \M2E\Kaufland\Model\Category\Dictionary[]
     */
    private function getCategories(int $fromId): array
    {
        $collection = $this->templateCategoryCollectionFactory->create();
        $collection->addFieldToFilter('id', ['gt' => $fromId]);
        $collection->setOrder('id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $collection->setPageSize(50);

        return array_values($collection->getItems());
    }

    /**
     * @param \M2E\Kaufland\Model\Category\Dictionary $category
     * @param \M2E\Kaufland\Model\AttributeMapping\Gpsr\Pair[] $gpsrAttributes
     *
     * @return void
     */
    private function processCategory(\M2E\Kaufland\Model\Category\Dictionary $category, array $gpsrAttributes): bool
    {
        $specificsByCode = $this->getSpecificsByCode($category);

        $isChangedCategory = false;
        foreach ($gpsrAttributes as $gpsrAttribute) {
            $attribute = $specificsByCode[$gpsrAttribute->channelAttributeCode] ?? null;

            if ($attribute === null) {
                continue;
            }

            if ($attribute->isValueModeNone()) {
                $attribute->setCustomAttributeValue($gpsrAttribute->magentoAttributeCode);
                $attribute->setValueCustomAttributeMode();
                $this->attributeRepository->save($attribute);

                $isChangedCategory = true;

                continue;
            }

            if (
                $attribute->isValueModeCustomAttribute()
                && $attribute->getCustomAttributeValue() !== $gpsrAttribute->magentoAttributeCode
            ) {
                $attribute->setCustomAttributeValue($gpsrAttribute->magentoAttributeCode);
                $this->attributeRepository->save($attribute);

                $isChangedCategory = true;
            }
        }

        return $isChangedCategory;
    }

    /**
     * @param \M2E\Kaufland\Model\Category\Dictionary $category
     *
     * @return \M2E\Kaufland\Model\Category\Attribute[]
     */
    private function getSpecificsByCode(\M2E\Kaufland\Model\Category\Dictionary $category): array
    {
        $result = [];

        $attributes = $category->getRelatedAttributes();
        foreach ($attributes as $attribute) {
            $result[$attribute->getAttributeNick()] = $attribute;
        }

        return $result;
    }

    private function createProductInstruction(\M2E\Kaufland\Model\Category\Dictionary $category): void
    {
        $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
        $affectedListingsProducts->setModel($category);

        $changeProcessor = $this->changeProcessorFactory->create();
        $changeProcessor->process(
            $this->categoryDiffStub,
            $affectedListingsProducts->getObjectsData(['id', 'status'])
        );
    }
}
