<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Dictionary;

class CreateService
{
    private \M2E\Kaufland\Model\Category\Tree\Repository $categoryTreeRepository;
    private \M2E\Kaufland\Model\Category\DictionaryFactory $dictionaryFactory;
    private \M2E\Kaufland\Model\Category\Tree\PathBuilder $pathBuilder;
    private \M2E\Kaufland\Model\Category\Dictionary\AttributeService $attributeService;
    private \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionaryRepository;

    public function __construct(
        \M2E\Kaufland\Model\Category\DictionaryFactory $dictionaryFactory,
        \M2E\Kaufland\Model\Category\Dictionary\AttributeService $attributeService,
        \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionaryRepository,
        \M2E\Kaufland\Model\Category\Tree\Repository $categoryTreeRepository,
        \M2E\Kaufland\Model\Category\Tree\PathBuilder $pathBuilder
    ) {
        $this->dictionaryFactory = $dictionaryFactory;
        $this->attributeService = $attributeService;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->pathBuilder = $pathBuilder;
        $this->categoryTreeRepository = $categoryTreeRepository;
    }

    public function create(
        \M2E\Kaufland\Model\Storefront $storefront,
        int $categoryId
    ): \M2E\Kaufland\Model\Category\Dictionary {
        $treeNode = $this->categoryTreeRepository
            ->getCategoryByStorefrontIdAndCategoryId($storefront->getId(), $categoryId);

        if ($treeNode === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Not found category tree');
        }

        $categoryData = $this->attributeService->getCategoryDataFromServer($storefront, $categoryId);

        $productAttributes = $this->attributeService->getAttributes($categoryData);
        $totalProductAttributes = $this->attributeService->getTotalProductAttributes($categoryData);
        $hasRequiredProductAttributes = $this->attributeService->getHasRequiredAttributes($categoryData);

        $dictionary = $this->dictionaryFactory->create()->create(
            $storefront->getId(),
            $categoryId,
            $this->pathBuilder->getPath($treeNode),
            $productAttributes,
            $totalProductAttributes,
            $hasRequiredProductAttributes
        );

        $this->categoryDictionaryRepository->create($dictionary);

        return $dictionary;
    }
}
