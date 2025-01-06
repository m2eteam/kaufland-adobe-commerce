<?php

namespace M2E\Kaufland\Model\ResourceModel\Processing;

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        $this->_init(
            \M2E\Kaufland\Model\Processing::class,
            \M2E\Kaufland\Model\ResourceModel\Processing::class
        );
    }
}
