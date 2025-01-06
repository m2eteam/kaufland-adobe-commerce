<?php

namespace M2E\Kaufland\Model\ResourceModel\Lock\Transactional;

/**
 * Class \M2E\Kaufland\Model\ResourceModel\Lock\Transactional\Collection
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init(
            \M2E\Kaufland\Model\Lock\Transactional::class,
            \M2E\Kaufland\Model\ResourceModel\Lock\Transactional::class
        );
    }

    //########################################
}
