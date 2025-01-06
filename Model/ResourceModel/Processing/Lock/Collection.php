<?php

namespace M2E\Kaufland\Model\ResourceModel\Processing\Lock;

/**
 * Class \M2E\Kaufland\Model\ResourceModel\Processing\Lock\Collection
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Processing\Lock::class,
            \M2E\Kaufland\Model\ResourceModel\Processing\Lock::class
        );
    }

    //########################################
}
