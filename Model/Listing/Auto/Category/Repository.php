<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Category;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\CollectionFactory $collectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category $resource;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\CollectionFactory $collectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category $resource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
    }

    /**
     * @return \M2E\Kaufland\Model\Listing\Auto\Category[]
     */
    public function getByGroupId(int $groupId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_GROUP_ID,
            ['eq' => $groupId]
        );

        return array_values($collection->getItems());
    }

    /**
     * @return \M2E\Kaufland\Model\Listing\Auto\Category[]
     */
    public function getByGroupIdAndCategoryId(int $groupId, int $categoryId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_GROUP_ID,
            ['eq' => $groupId]
        );
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_CATEGORY_ID,
            ['eq' => $categoryId]
        );

        return array_values($collection->getItems());
    }

    public function getSelectedCategoriesIds(int $groupId): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_GROUP_ID,
                ['eq' => $groupId]
            );
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_CATEGORY_ID,
            ['neq' => 0]
        );

        return $collection
            ->getColumnValues(\M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_CATEGORY_ID);
    }

    public function delete(\M2E\Kaufland\Model\Listing\Auto\Category $category): void
    {
        $this->resource->delete($category);
    }

    public function create(\M2E\Kaufland\Model\Listing\Auto\Category $category)
    {
        $this->resource->save($category);
    }

    public function deleteByCategoryGroupId(int $groupId): void
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_GROUP_ID,
                ['eq' => $groupId]
            );

        foreach ($collection->getItems() as $item) {
            $this->delete($item);
        }
    }
}
