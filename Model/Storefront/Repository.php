<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Storefront;

class Repository
{
    use \M2E\Kaufland\Model\CacheTrait;

    private \M2E\Kaufland\Model\StorefrontFactory $entityFactory;
    private \M2E\Kaufland\Model\ResourceModel\Storefront\CollectionFactory $collectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Storefront $resource;
    private \M2E\Kaufland\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\Kaufland\Model\StorefrontFactory $entityFactory,
        \M2E\Kaufland\Model\ResourceModel\Storefront\CollectionFactory $collectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Storefront $resource,
        \M2E\Kaufland\Helper\Data\Cache\Permanent $cache
    ) {
        $this->entityFactory = $entityFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->cache = $cache;
    }

    public function find(int $id): ?\M2E\Kaufland\Model\Storefront
    {
        $storefront = $this->entityFactory->create();

        $cachedData = $this->cache->getValue($this->makeCacheKey($storefront, $id));
        if (!empty($cachedData)) {
            $this->initializeFromCache($storefront, $cachedData);

            return $storefront;
        }

        $this->resource->load($storefront, $id);

        if ($storefront->isObjectNew()) {
            return null;
        }

        $this->cache->setValue(
            $this->makeCacheKey($storefront, $id),
            $this->getCacheDate($storefront),
            [],
            60 * 60
        );

        return $storefront;
    }

    public function get(int $storefrontId): \M2E\Kaufland\Model\Storefront
    {
        $storefront = $this->find($storefrontId);
        if ($storefront === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Storefront not found.');
        }

        return $storefront;
    }

    public function getByCode(string $code): \M2E\Kaufland\Model\Storefront
    {
        $storefront = $this->findByCode($code);
        if ($storefront === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic("Storefront [$code] not found.");
        }

        return $storefront;
    }

    public function findByCode(string $code): ?\M2E\Kaufland\Model\Storefront
    {
        $storefront = $this->entityFactory->create();
        $this->resource->loadByCode($storefront, $code);

        return $storefront;
    }

    /**
     * @return \M2E\Kaufland\Model\Storefront[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }

    /**
     * @param int $accountId
     *
     * @return \M2E\Kaufland\Model\Storefront[]
     */
    public function findForAccount(int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('account_id', $accountId);

        return array_values($collection->getItems());
    }

    public function create(\M2E\Kaufland\Model\Storefront $storefront): void
    {
        $this->resource->save($storefront);
    }

    public function save(\M2E\Kaufland\Model\Storefront $storefront): void
    {
        $this->resource->save($storefront);
        $this->cache->removeValue($this->makeCacheKey($storefront, $storefront->getId()));
    }

    public function remove(\M2E\Kaufland\Model\Storefront $storefront): void
    {
        $this->resource->delete($storefront);
        $this->cache->removeValue($this->makeCacheKey($storefront, $storefront->getId()));
    }
}
