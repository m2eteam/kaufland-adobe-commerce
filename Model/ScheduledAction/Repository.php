<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ScheduledAction;

use M2E\Kaufland\Model\ResourceModel\ScheduledAction as ScheduledActionResource;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\ScheduledAction $resource;
    private \M2E\Kaufland\Model\ResourceModel\ScheduledAction\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\ScheduledAction $resource,
        \M2E\Kaufland\Model\ResourceModel\ScheduledAction\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
    }

    public function create(\M2E\Kaufland\Model\ScheduledAction $action): void
    {
        if ($action instanceof \M2E\Kaufland\Model\ScheduledAction\Stub) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Unable create stub object.');
        }

        $this->resource->save($action);
    }

    /**
     * @param \M2E\Kaufland\Model\ScheduledAction[] $ids
     *
     * @return array
     */
    public function getByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $collection  = $this->collectionFactory->create();
        $collection->addFieldToFilter(ScheduledActionResource::COLUMN_ID, array_unique($ids));

        return array_values($collection->getItems());
    }

    public function findByListingProductId(int $listingProductId): ?\M2E\Kaufland\Model\ScheduledAction
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID, $listingProductId);

        /** @var \M2E\Kaufland\Model\ScheduledAction $item */
        $item = $collection->getFirstItem();
        if ($item->isObjectNew()) {
            return null;
        }

        return $item;
    }

    public function findByListingProductIdAndType(int $listingProductId, int $actionType): ?\M2E\Kaufland\Model\ScheduledAction
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID, $listingProductId);
        $collection->addFieldToFilter(ScheduledActionResource::COLUMN_ACTION_TYPE, $actionType);

        /** @var \M2E\Kaufland\Model\ScheduledAction $item */
        $item = $collection->getFirstItem();
        if ($item->isObjectNew()) {
            return null;
        }

        return $item;
    }

    public function remove(\M2E\Kaufland\Model\ScheduledAction $action): void
    {
        if ($action instanceof \M2E\Kaufland\Model\ScheduledAction\Stub) {
            return;
        }

        $this->resource->delete($action);
    }
}
