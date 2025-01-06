<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Attribute;

use M2E\Kaufland\Model\Category\Attribute;
use M2E\Kaufland\Model\ResourceModel\Category\Attribute as AttributeResource;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\Category\Attribute\CollectionFactory $attributeCollectionFactory;
    private AttributeResource $attributeResource;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Category\Attribute\CollectionFactory $attributeCollectionFactory,
        AttributeResource $attributeResource
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attributeResource = $attributeResource;
    }

    public function create(\M2E\Kaufland\Model\Category\Attribute $entity): void
    {
        $this->attributeResource->save($entity);
    }

    public function save(\M2E\Kaufland\Model\Category\Attribute $attrEntity): void
    {
        $this->attributeResource->save($attrEntity);
    }

    /**
     * @return Attribute[]
     */
    public function findByDictionaryId(
        int $dictionaryId,
        array $typeFilter = []
    ): array {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter(
            AttributeResource::COLUMN_CATEGORY_DICTIONARY_ID,
            ['eq' => $dictionaryId]
        );

        return array_values($collection->getItems());
    }

    public function getCountByDictionaryId(int $dictionaryId): int
    {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter(
            AttributeResource::COLUMN_CATEGORY_DICTIONARY_ID,
            $dictionaryId
        );

        return $collection->getSize();
    }
}
