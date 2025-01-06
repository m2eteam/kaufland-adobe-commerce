<?php

namespace M2E\Kaufland\Model\ResourceModel\Category\Dictionary;

/**
 * @method \M2E\Kaufland\Model\Category\Dictionary getFirstItem()
 * @method \M2E\Kaufland\Model\Category\Dictionary[] getItems()
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Category\Dictionary::class,
            \M2E\Kaufland\Model\ResourceModel\Category\Dictionary::class
        );
    }
}
