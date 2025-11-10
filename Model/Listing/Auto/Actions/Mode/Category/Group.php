<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Actions\Mode\Category;

class Group
{
    private int $listingId;
    private array $categoryIds;
    private array $autoCategoryGroupIds;

    public function __construct(int $listingId, array $categoryIds, array $autoCategoryGroupIds)
    {
        $this->listingId = $listingId;
        $this->categoryIds = $categoryIds;
        $this->autoCategoryGroupIds = $autoCategoryGroupIds;
    }

    public function isContainsCategoryIds(array $categoryIds): bool
    {
        foreach ($categoryIds as $id) {
            if (in_array($id, $this->getCategoryIds())) {
                return true;
            }
        }

        return false;
    }

    public function getListingId(): int
    {
        return $this->listingId;
    }

    public function getCategoryIds(): array
    {
        return $this->categoryIds;
    }

    public function getAutoCategoryGroupIds(): array
    {
        return $this->autoCategoryGroupIds;
    }
}
