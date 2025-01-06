<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category;

use M2E\Kaufland\Model\ResourceModel\Category\Tree as CategoryTreeResource;

class Tree extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(CategoryTreeResource::class);
    }

    public function create(
        int $storefrontEntityId,
        int $categoryId,
        ?int $parentCategoryId,
        string $title
    ): self {
        $this->setData(CategoryTreeResource::COLUMN_STOREFRONT_ID, $storefrontEntityId);
        $this->setData(CategoryTreeResource::COLUMN_CATEGORY_ID, $categoryId);
        $this->setData(CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID, $parentCategoryId);
        $this->setData(CategoryTreeResource::COLUMN_TITLE, $title);

        return $this;
    }

    public function getStorefrontId(): int
    {
        return (int)$this->getData(CategoryTreeResource::COLUMN_STOREFRONT_ID);
    }

    public function getCategoryId(): int
    {
        return (int)$this->getData(CategoryTreeResource::COLUMN_CATEGORY_ID);
    }

    public function getTitle(): string
    {
        return $this->getData(CategoryTreeResource::COLUMN_TITLE);
    }

    public function getParentCategoryId(): ?int
    {
        if ($parentCategoryId = $this->getDataByKey(CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID)) {
            return (int)$parentCategoryId;
        }

        return null;
    }
}
