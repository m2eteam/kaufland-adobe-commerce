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

    /**
     * @return Attribute[]
     */
    public function findByDictionaryIdAndAttributeIds(
        int $dictionaryId,
        array $attributeIds
    ): array {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter(
            AttributeResource::COLUMN_CATEGORY_DICTIONARY_ID,
            ['eq' => $dictionaryId]
        );
        $collection->addFieldToFilter(
            AttributeResource::COLUMN_ATTRIBUTE_ID,
            ['in' => $attributeIds]
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

    /**
     * @return string[]
     */
    public function getAllCustomAttributeNicks(): array
    {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter(
            AttributeResource::COLUMN_VALUE_MODE,
            \M2E\Kaufland\Model\Category\Attribute::VALUE_MODE_CUSTOM_ATTRIBUTE
        );

        $collection->removeAllFieldsFromSelect();

        $collection->addFieldToSelect(AttributeResource::COLUMN_ATTRIBUTE_NICK);
        $collection->distinct(true);

        $result = [];
        /** @var \M2E\Kaufland\Model\Category\Attribute $item */
        foreach ($collection->getItems() as $item) {
            $result[] = $item->getAttributeNick();
        }

        return $result;
    }

    /**
     * @param int $dictionaryId
     *
     * @return Attribute[]
     */
    public function getAttributesWithCustomValue(int $dictionaryId): array
    {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter(
            AttributeResource::COLUMN_CATEGORY_DICTIONARY_ID,
            ['eq' => $dictionaryId]
        );

        $collection->addFieldToFilter(
            AttributeResource::COLUMN_VALUE_MODE,
            \M2E\Kaufland\Model\Category\Attribute::VALUE_MODE_CUSTOM_ATTRIBUTE
        );

        return array_values($collection->getItems());
    }
}
