<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Category\Group;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group\CollectionFactory $collectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group $resource;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group\CollectionFactory $collectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group $resource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
    }

    public function get(int $id): \M2E\Kaufland\Model\Listing\Auto\Category\Group
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group::COLUMN_ID,
            ['eq' => $id]
        );

        $group = $collection->getFirstItem();
        if ($group->isObjectNew()) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Auto Group not found');
        }

        return $group;
    }

    public function isTitleUnique($title, int $listingId, ?int $groupId): bool
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group::COLUMN_LISTING_ID,
            ['eq' => $listingId]
        );
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group::COLUMN_TITLE,
            ['eq' => $title]
        );

        if ($groupId !== null) {
            $collection->addFieldToFilter(
                \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group::COLUMN_ID,
                ['neq' => $groupId]
            );
        }

        return $collection->getSize() === 0;
    }

    public function save(\M2E\Kaufland\Model\Listing\Auto\Category\Group $categoryGroup): void
    {
        $this->resource->save($categoryGroup);
    }

    public function create(\M2E\Kaufland\Model\Listing\Auto\Category\Group $categoryGroup): void
    {
        $this->resource->save($categoryGroup);
    }

    public function delete(\M2E\Kaufland\Model\Listing\Auto\Category\Group $categoryGroup)
    {
        $this->resource->delete($categoryGroup);
    }

    /**
     * @return \M2E\Kaufland\Model\Listing\Auto\Category\Group[]
     */
    public function getByListingId(int $listingId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group::COLUMN_LISTING_ID,
            ['eq' => $listingId]
        );

        return array_values($collection->getItems());
    }
}
