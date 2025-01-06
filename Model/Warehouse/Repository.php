<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Warehouse;

use M2E\Kaufland\Model\ResourceModel\Warehouse as WarehouseResource;

class Repository
{
    use \M2E\Kaufland\Model\CacheTrait;

    private WarehouseResource\CollectionFactory $collectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Warehouse $resource;
    private \M2E\Kaufland\Model\WarehouseFactory $warehouseFactory;
    private \M2E\Kaufland\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Warehouse\CollectionFactory $collectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Warehouse $resource,
        \M2E\Kaufland\Model\WarehouseFactory $warehouseFactory,
        \M2E\Kaufland\Helper\Data\Cache\Permanent $cache
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->warehouseFactory = $warehouseFactory;
        $this->cache = $cache;
    }

    public function find(int $id): ?\M2E\Kaufland\Model\Warehouse
    {
        $warehouse = $this->warehouseFactory->create();

        $cacheData = $this->cache->getValue($this->makeCacheKey($warehouse, $id));
        if (!empty($cacheData)) {
            $this->initializeFromCache($warehouse, $cacheData);

            return $warehouse;
        }

        $this->resource->load($warehouse, $id);

        if ($warehouse->isObjectNew()) {
            return null;
        }

        $this->cache->setValue(
            $this->makeCacheKey($warehouse, $id),
            $this->getCacheDate($warehouse),
            [],
            60 * 60
        );

        return $warehouse;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function get(int $id): \M2E\Kaufland\Model\Warehouse
    {
        $warehouse = $this->find($id);
        if ($warehouse === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Shipping Group not found');
        }

        return $warehouse;
    }

    public function getByWarehouseId(int $warehouseId): \M2E\Kaufland\Model\Warehouse
    {
        $warehouse = $this->findByWarehouseId($warehouseId);
        if ($warehouse === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic("Warehouse [$warehouseId] not found.");
        }

        return $warehouse;
    }

    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }

    public function findByWarehouseId(int $warehouseId): ?\M2E\Kaufland\Model\Warehouse
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(WarehouseResource::COLUMN_WAREHOUSE_ID, $warehouseId);

        /** @var \M2E\Kaufland\Model\Warehouse $entity */
        $entity = $collection->getFirstItem();

        return $entity->isObjectNew() ? null : $entity;
    }

    public function create(\M2E\Kaufland\Model\Warehouse $warehouse): void
    {
        $this->resource->save($warehouse);
    }

    public function save(\M2E\Kaufland\Model\Warehouse $warehouse): void
    {
        $this->resource->save($warehouse);
        $this->cache->removeValue($this->makeCacheKey($warehouse, $warehouse->getId()));
    }

    public function removeByAccountId(int $accountId): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            [WarehouseResource::COLUMN_ACCOUNT_ID . ' =?' => $accountId]
        );
    }

    /**
     * @param int $accountId
     *
     * @return \M2E\Kaufland\Model\Warehouse[]
     */
    public function findByAccount(int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(WarehouseResource::COLUMN_ACCOUNT_ID, $accountId);

        return array_values($collection->getItems());
    }
}
