<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Actions\Mode\Category;

class GroupSet
{
    /** @var Group[] */
    private array $groups = [];

    /**
     * @param Group[] $groups
     */
    public function __construct(array $groups = [])
    {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }
    }

    public function excludeGroupsThatContainsCategoryIds(array $categoryIds): self
    {
        return $this->filter(function (Group $group) use ($categoryIds) {
            return !$group->isContainsCategoryIds($categoryIds);
        });
    }

    public function fillGroupData(int $listingId, array $categoryIds, array $autoCategoryGroupIds): void
    {
        $categoryIds = array_unique($categoryIds);
        $autoCategoryGroupIds = array_unique($autoCategoryGroupIds);
        sort($categoryIds);
        sort($autoCategoryGroupIds);
        $this->addGroup(new Group($listingId, $categoryIds, $autoCategoryGroupIds));
    }

    public function filter(callable $callback): self
    {
        $groups = array_filter($this->groups, $callback);

        return new self($groups);
    }

    public function isEmpty(): bool
    {
        return empty($this->getGroups());
    }

    public function addGroup(Group $group): void
    {
        $this->groups[] = $group;
    }

    /**
     * @return Group[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
