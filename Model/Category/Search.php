<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category;

use M2E\Kaufland\Model\Category\Search\ResultCollection;
use M2E\Kaufland\Model\Category\Search\ResultItem;

class Search
{
    private \M2E\Kaufland\Model\Category\Tree\Repository $categoryRepository;
    private \M2E\Kaufland\Model\Category\Tree\PathBuilder $pathBuilder;
    private \M2E\Kaufland\Model\Category\Tree\Manager $categoryTreeManager;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;

    public function __construct(
        \M2E\Kaufland\Model\Category\Tree\Repository $categoryRepository,
        \M2E\Kaufland\Model\Category\Tree\PathBuilder $pathBuilder,
        \M2E\Kaufland\Model\Category\Tree\Manager $categoryTreeManager,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository
    ) {
        $this->storefrontRepository = $storefrontRepository;
        $this->categoryTreeManager = $categoryTreeManager;
        $this->categoryRepository = $categoryRepository;
        $this->pathBuilder = $pathBuilder;
    }

    public function process(int $storefrontId, string $searchQuery, int $limit): ResultCollection
    {
        $resultCollection = new ResultCollection($limit);
        $foundedItems = $this->categoryRepository->searchByTitleOrId($storefrontId, $searchQuery, $limit);
        if (count($foundedItems) === 0) {
            return $resultCollection;
        }

        foreach ($foundedItems as $item) {
            $storefront = $this->storefrontRepository->find($item->getStorefrontId());
            $isLeaf = count($this->categoryTreeManager->getCategories($storefront, $item->getCategoryId())) === 0;
            if ($isLeaf) {
                $this->addLeafItem($resultCollection, $item);

                continue;
            }

            $this->addCategoryChildren($resultCollection, $item);
            if ($resultCollection->getCount() > $limit) {
                break;
            }
        }

        return $resultCollection;
    }

    private function addLeafItem(ResultCollection $resultCollection, Tree $treeItem): void
    {
        $resultCollection->add(
            new ResultItem(
                $treeItem->getCategoryId(),
                $this->pathBuilder->getPath($treeItem)
            )
        );
    }

    private function addCategoryChildren(ResultCollection $resultCollection, Tree $treeItem): void
    {
        $children = $this->categoryRepository
            ->getChildren($treeItem->getStorefrontId(), $treeItem->getCategoryId());

        foreach ($children as $child) {
            $this->addLeafItem($resultCollection, $child);
        }
    }
}
