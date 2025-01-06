<?php

namespace M2E\Kaufland\Model\ResourceModel\Tag;

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    /**
     * @inerhitDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Tag\Entity::class,
            \M2E\Kaufland\Model\ResourceModel\Tag::class
        );
    }

    /**
     * @return \M2E\Kaufland\Model\Tag\Entity[]
     */
    public function getItemsWithoutHasErrorsTag(): array
    {
        $this->getSelect()->where('error_code != (?)', \M2E\Kaufland\Model\Tag::HAS_ERROR_ERROR_CODE);

        return $this->getAll();
    }

    /**
     * @return \M2E\Kaufland\Model\Tag\Entity[]
     */
    public function getAll(): array
    {
        return $this->getItems();
    }
}
