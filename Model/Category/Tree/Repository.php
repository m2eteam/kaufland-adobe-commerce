<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Tree;

use M2E\Kaufland\Model\Category\Tree;
use M2E\Kaufland\Model\ResourceModel\Category\Tree as CategoryTreeResource;

class Repository
{
    private CategoryTreeResource\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Category\Tree\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return Tree[]
     */
    public function getRootCategories(int $storefrontId): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_STOREFRONT_ID,
            ['eq' => $storefrontId]
        );
        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID,
            [
                ['eq' => 1]
            ]
        );

        return array_values($collection->getItems());
    }

    public function getCategoryByStorefrontIdAndCategoryId(int $storefrontId, int $categoryId): ?Tree
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_STOREFRONT_ID,
            ['eq' => $storefrontId]
        );
        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_CATEGORY_ID,
            ['eq' => $categoryId]
        );

        /** @var Tree $entity */
        $entity = $collection->getFirstItem();

        if ($entity->isObjectNew()) {
            return null;
        }

        return $entity;
    }

    /**
     * @param int $storefrontId
     * @param int $parentCategoryId
     *
     * @return Tree[]
     */
    public function getChildCategories(int $storefrontId, int $parentCategoryId): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_STOREFRONT_ID,
            ['eq' => $storefrontId]
        );
        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID,
            ['eq' => $parentCategoryId]
        );

        return array_values($collection->getItems());
    }

    /**
     * @param Tree $entity
     *
     * @return Tree[]
     */
    public function getParents(Tree $entity): array
    {
        $ancestors = $this->getRecursivelyParents($entity);

        return array_reverse($ancestors);
    }

    /**
     * @param Tree[] $ancestors
     *
     * @return Tree[]
     */
    private function getRecursivelyParents(Tree $child, array $ancestors = []): array
    {
        if ($child->getParentCategoryId() === null) {
            return $ancestors;
        }

        $parent = $this->getCategoryByStorefrontIdAndCategoryId(
            $child->getStorefrontId(),
            $child->getParentCategoryId()
        );
        if ($parent === null) {
            return $ancestors;
        }

        $ancestors[] = $parent;

        return $this->getRecursivelyParents($parent, $ancestors);
    }

    /**
     * @param \M2E\Kaufland\Model\Category\Tree[] $categories
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function batchInsert(array $categories): void
    {
        $insertData = [];
        foreach ($categories as $category) {
            $insertData[] = [
                CategoryTreeResource::COLUMN_STOREFRONT_ID => $category->getStorefrontId(),
                CategoryTreeResource::COLUMN_CATEGORY_ID => $category->getCategoryId(),
                CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID => $category->getParentCategoryId(),
                CategoryTreeResource::COLUMN_TITLE => $category->getTitle(),
            ];
        }

        $collection = $this->collectionFactory->create();
        $resource = $collection->getResource();

        foreach (array_chunk($insertData, 500) as $chunk) {
            $resource->getConnection()->insertMultiple($resource->getMainTable(), $chunk);
        }
    }

    public function deleteByStorefrontId(int $storefrontId): void
    {
        $collection = $this->collectionFactory->create();
        $connection = $collection->getConnection();
        $connection->delete(
            $collection->getMainTable(),
            sprintf('%s = %s', CategoryTreeResource::COLUMN_STOREFRONT_ID, $storefrontId)
        );
    }

    /**
     * @return Tree[]
     */
    public function searchByTitleOrId(int $storefrontId, string $query, int $limit): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_STOREFRONT_ID,
            ['eq' => $storefrontId]
        );

        $collection->addFieldToFilter(
            [CategoryTreeResource::COLUMN_TITLE, CategoryTreeResource::COLUMN_CATEGORY_ID],
            [['like' => "%$query%"], ['like' => "%$query%"]]
        );

        $collection->setPageSize($limit);

        return array_values($collection->getItems());
    }

    /**
     * @return Tree[]
     */
    public function getChildren(int $storefrontId, int $parentCategoryId): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID,
            ['eq' => $parentCategoryId]
        );

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_STOREFRONT_ID,
            ['eq' => $storefrontId]
        );

        $collection->getSelect()->order([
            CategoryTreeResource::COLUMN_CATEGORY_ID,
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID
        ]);

        return array_values($collection->getItems());
    }
}
