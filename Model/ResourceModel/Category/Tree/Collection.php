<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Category\Tree;

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Category\Tree::class,
            \M2E\Kaufland\Model\ResourceModel\Category\Tree::class
        );
    }
}
