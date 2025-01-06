<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Dictionary;

use M2E\Kaufland\Model\ResourceModel\Category\Dictionary as DictionaryResource;

class Repository
{
    private DictionaryResource\CollectionFactory $dictionaryCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Category\Dictionary $categoryDictionaryResource;
    private \M2E\Kaufland\Model\Category\DictionaryFactory $dictionaryFactory;

    public function __construct(
        DictionaryResource\CollectionFactory $dictionaryCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Category\Dictionary $categoryDictionaryResource,
        \M2E\Kaufland\Model\Category\DictionaryFactory $dictionaryFactory
    ) {
        $this->dictionaryCollectionFactory = $dictionaryCollectionFactory;
        $this->categoryDictionaryResource = $categoryDictionaryResource;
        $this->dictionaryFactory = $dictionaryFactory;
    }

    public function create(\M2E\Kaufland\Model\Category\Dictionary $dictionary): void
    {
        $dictionary->setUpdateDate(\M2E\Core\Helper\Date::createCurrentGmt());
        $dictionary->setCreateDate(\M2E\Core\Helper\Date::createCurrentGmt());

        $this->categoryDictionaryResource->save($dictionary);
    }

    public function save(\M2E\Kaufland\Model\Category\Dictionary $dictionary): void
    {
        $dictionary->setUpdateDate(\M2E\Core\Helper\Date::createCurrentGmt());

        $this->categoryDictionaryResource->save($dictionary);
    }

    public function get(int $id): \M2E\Kaufland\Model\Category\Dictionary
    {
        $entity = $this->find($id);
        if ($entity === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Not found dictionary with id ' . $id);
        }

        return $entity;
    }

    public function find(int $id): ?\M2E\Kaufland\Model\Category\Dictionary
    {
        $dictionary = $this->dictionaryFactory->create();
        $this->categoryDictionaryResource->load($dictionary, $id);

        if ($dictionary->isObjectNew()) {
            return null;
        }

        return $dictionary;
    }

    /**
     * @param int|string[] $ids
     *
     * @return \M2E\Kaufland\Model\Category\Dictionary[]
     */
    public function getItems(array $ids): array
    {
        $collection = $this->dictionaryCollectionFactory->create();
        $collection->addFieldToFilter(DictionaryResource::COLUMN_ID, ['in' => $ids]);

        return array_values($collection->getItems());
    }

    /**
     * @return \M2E\Kaufland\Model\Category\Dictionary[]
     */
    public function getAllItems(): array
    {
        $collection = $this->dictionaryCollectionFactory->create();

        return array_values($collection->getItems());
    }

    /**
     * @return \M2E\Kaufland\Model\Category\Dictionary[]
     */
    public function getByStorefrontId(int $storefrontId): array
    {
        $collection = $this->dictionaryCollectionFactory->create();
        $collection->addFieldToFilter(DictionaryResource::COLUMN_STOREFRONT_ID, ['eq' => $storefrontId]);

        return array_values($collection->getItems());
    }

    public function findByStorefrontAndCategoryId(
        int $storefrontId,
        int $categoryId
    ): ?\M2E\Kaufland\Model\Category\Dictionary {
        $collection = $this->dictionaryCollectionFactory->create();
        $collection
            ->addFieldToFilter(DictionaryResource::COLUMN_CATEGORY_ID, $categoryId)
            ->addFieldToFilter(DictionaryResource::COLUMN_STOREFRONT_ID, $storefrontId);

        $entity = $collection->getFirstItem();

        return $entity->isObjectNew() ? null : $entity;
    }

    public function delete(\M2E\Kaufland\Model\Category\Dictionary $dictionary): void
    {
        $this->categoryDictionaryResource->delete($dictionary);
    }
}
