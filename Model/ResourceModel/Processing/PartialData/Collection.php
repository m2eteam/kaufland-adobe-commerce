<?php

namespace M2E\Kaufland\Model\ResourceModel\Processing\PartialData;

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Processing\PartialData::class,
            \M2E\Kaufland\Model\ResourceModel\Processing\PartialData::class
        );
    }
}
