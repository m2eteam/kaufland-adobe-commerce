<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\AttributeMapping;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\AttributeMapping\Pair $resource;
    private \M2E\Kaufland\Model\ResourceModel\AttributeMapping\Pair\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\AttributeMapping\Pair $resource,
        \M2E\Kaufland\Model\ResourceModel\AttributeMapping\Pair\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
    }

    public function create(\M2E\Kaufland\Model\AttributeMapping\Pair $pair): void
    {
        $this->resource->save($pair);
    }

    public function save(\M2E\Kaufland\Model\AttributeMapping\Pair $pair): void
    {
        $this->resource->save($pair);
    }

    public function remove(\M2E\Kaufland\Model\AttributeMapping\Pair $pair): void
    {
        $this->resource->delete($pair);
    }

    /**
     * @param string $type
     *
     * @return \M2E\Kaufland\Model\AttributeMapping\Pair[]
     */
    public function findByType(string $type): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\M2E\Kaufland\Model\ResourceModel\AttributeMapping\Pair::COLUMN_TYPE, ['eq' => $type]);

        return array_values($collection->getItems());
    }
}
