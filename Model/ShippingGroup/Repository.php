<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ShippingGroup;

use M2E\Kaufland\Model\ResourceModel\ShippingGroup as ShippingGroupResource;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\ShippingGroup\CollectionFactory $collectionFactory;
    private \M2E\Kaufland\Model\ShippingGroupFactory $shippingGroupFactory;
    /** @var \M2E\Kaufland\Model\ResourceModel\ShippingGroup */
    private ShippingGroupResource $resource;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\ShippingGroup\CollectionFactory $collectionFactory,
        \M2E\Kaufland\Model\ShippingGroupFactory $shippingGroupFactory,
        \M2E\Kaufland\Model\ResourceModel\ShippingGroup $resource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->shippingGroupFactory = $shippingGroupFactory;
        $this->resource = $resource;
    }

    public function create(\M2E\Kaufland\Model\ShippingGroup $shippingProvider): void
    {
        $shippingProvider->save();
    }

    public function save(\M2E\Kaufland\Model\ShippingGroup $shippingProvider): void
    {
        $shippingProvider->save();
    }

    /**
     * @param int $accountId
     *
     * @return \M2E\Kaufland\Model\ResourceModel\ShippingGroup[]
     */
    public function findByAccount(int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ShippingGroupResource::COLUMN_ACCOUNT_ID, $accountId);

        return array_values($collection->getItems());
    }

    public function removeByAccountId(int $accountId): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            ['account_id = ?' => $accountId],
        );
    }

    public function findByStorefrontId(int $storefrontId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ShippingGroupResource::COLUMN_STOREFRONT_ID, $storefrontId);

        return array_values($collection->getItems());
    }

    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }

    public function find(int $id): ?\M2E\Kaufland\Model\ShippingGroup
    {
        $model = $this->shippingGroupFactory->create();
        $this->resource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function get(int $id): \M2E\Kaufland\Model\ShippingGroup
    {
        $shippingGroup = $this->find($id);
        if ($shippingGroup === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Shipping Group not found');
        }

        return $shippingGroup;
    }

    /**
     * @param int $shippingGroupId
     * @param int $storefrontId
     *
     * @return bool
     */
    public function isShippingGroupExistsByStorefront(int $shippingGroupId, int $storefrontId): bool
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(ShippingGroupResource::COLUMN_STOREFRONT_ID, $storefrontId)
                   ->addFieldToFilter(ShippingGroupResource::COLUMN_ID, $shippingGroupId);

        return (bool) $collection->getSize();
    }
}
