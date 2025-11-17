<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\AdvancedFilter;

/**
 * @method \M2E\Kaufland\Model\AdvancedFilter[] getItems()
 * @method \M2E\Kaufland\Model\AdvancedFilter getFirstItem()
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\AdvancedFilter::class,
            \M2E\Kaufland\Model\ResourceModel\AdvancedFilter::class
        );
    }

    /**
     * @return \M2E\Kaufland\Model\AdvancedFilter[]
     */
    public function getAll(): array
    {
        return $this->getItems();
    }
}
