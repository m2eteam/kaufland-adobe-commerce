<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Dictionary;

class UpdateService
{
    private \M2E\Kaufland\Model\Category\Dictionary\AttributeService $attributeService;
    private \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionaryRepository;

    public function __construct(
        \M2E\Kaufland\Model\Category\Dictionary\AttributeService $attributeService,
        \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionaryRepository
    ) {
        $this->attributeService = $attributeService;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
    }

    public function update(
        \M2E\Kaufland\Model\Category\Dictionary $dictionary
    ): \M2E\Kaufland\Model\Category\Dictionary {
        $storefront = $dictionary->getStorefront();
        $categoryId = $dictionary->getCategoryId();

        $categoryData = $this->attributeService->getCategoryDataFromServer($storefront, $categoryId);

        $productAttributes = $this->attributeService->getAttributes($categoryData);
        $totalProductAttributes = $this->attributeService->getTotalProductAttributes($categoryData);
        $hasRequiredProductAttributes = $this->attributeService->getHasRequiredAttributes($categoryData);

        $dictionary->setProductAttributes($productAttributes);
        $dictionary->setTotalProductAttributes($totalProductAttributes);
        $dictionary->setHasRequiredProductAttributes($hasRequiredProductAttributes);

        $this->categoryDictionaryRepository->save($dictionary);

        return $dictionary;
    }
}
