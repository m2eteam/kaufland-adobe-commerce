<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Listing\Other;

/**
 * @method \M2E\Kaufland\Model\Listing\Other[] getItems()
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Listing\Other::class,
            \M2E\Kaufland\Model\ResourceModel\Listing\Other::class
        );
    }
}
