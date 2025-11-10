<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto;

class Category extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group $group;
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $groupRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $groupRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\M2E\Kaufland\Model\Factory $modelFactory = null,
        ?\M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory = null,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->groupRepository = $groupRepository;
        parent::__construct(
            $context,
            $registry,
            $modelFactory,
            $activeRecordFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::class);
    }

    public function init(int $groupId, int $categoryId): self
    {
        $this->setData(\M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_GROUP_ID, $groupId);
        $this->setData(\M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_CATEGORY_ID, $categoryId);

        return $this;
    }

    public function getGroupId(): int
    {
        return (int)$this->getData(\M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_GROUP_ID);
    }

    public function getCategoryId(): int
    {
        return (int)$this->getData(\M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_CATEGORY_ID);
    }

    public function getCategoryGroup(): Category\Group
    {
        if ($this->getGroupId() <= 0) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Group ID was not set.');
        }

        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->group)) {
            $this->group = $this->groupRepository->get($this->getGroupId());
        }

        return $this->group;
    }
}
