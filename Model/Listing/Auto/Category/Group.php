<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Category;

use M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group as GroupResource;

class Group extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    private \M2E\Kaufland\Model\Listing\Auto\Category\Repository $categoryRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Auto\Category\Repository $categoryRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\M2E\Kaufland\Model\Factory $modelFactory = null,
        ?\M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory = null,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $modelFactory,
            $activeRecordFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->categoryRepository = $categoryRepository;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(GroupResource::class);
    }

    public function init(
        string $title,
        int $listingId,
        int $addingMode,
        int $addingAddNotVisible,
        int $deletingMode,
        int $addingTemplateCategoryId
    ): self {
        $this->setData(GroupResource::COLUMN_TITLE, $title);
        $this->setData(GroupResource::COLUMN_LISTING_ID, $listingId);
        $this->setData(GroupResource::COLUMN_ADDING_MODE, $addingMode);
        $this->setData(GroupResource::COLUMN_ADDING_ADD_NOT_VISIBLE, $addingAddNotVisible);
        $this->setData(GroupResource::COLUMN_DELETING_MODE, $deletingMode);
        $this->setData(GroupResource::COLUMN_ADDING_TEMPLATE_CATEGORY_ID, $addingTemplateCategoryId);

        return $this;
    }

    public function getListingId(): int
    {
        return (int)$this->getData(GroupResource::COLUMN_LISTING_ID);
    }

    public function getTitle(): string
    {
        return (string)$this->getData(GroupResource::COLUMN_TITLE);
    }

    public function getAddingMode(): int
    {
        return (int)$this->getData(GroupResource::COLUMN_ADDING_MODE);
    }

    public function isAddingModeNone(): bool
    {
        return $this->getAddingMode() == \M2E\Kaufland\Model\Listing::ADDING_MODE_NONE;
    }

    public function isAddingAddNotVisibleYes(): bool
    {
        return $this->getData(GroupResource::COLUMN_ADDING_ADD_NOT_VISIBLE)
            == \M2E\Kaufland\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES;
    }

    public function getAddingTemplateCategoryId(): int
    {
        return (int)$this->getData(GroupResource::COLUMN_ADDING_TEMPLATE_CATEGORY_ID);
    }

    public function getDeletingMode(): int
    {
        return (int)$this->getData(GroupResource::COLUMN_DELETING_MODE);
    }

    public function isDeletingModeNone(): bool
    {
        return $this->getDeletingMode() === \M2E\Kaufland\Model\Listing::DELETING_MODE_NONE;
    }

    /**
     * @return \M2E\Kaufland\Model\Listing\Auto\Category[]
     */
    public function getCategories(): array
    {
        return $this->categoryRepository->getByGroupId($this->getId());
    }

    public function delete()
    {
        foreach ($this->getCategories() as $category) {
            $category->delete();
        }

        return parent::delete();
    }

    public function hasCategories(): bool
    {
        return count($this->getCategories()) !== 0;
    }
}
