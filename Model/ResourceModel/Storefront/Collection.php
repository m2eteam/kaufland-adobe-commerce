<?php

namespace M2E\Kaufland\Model\ResourceModel\Storefront;

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Storefront::class,
            \M2E\Kaufland\Model\ResourceModel\Storefront::class
        );
    }
}
