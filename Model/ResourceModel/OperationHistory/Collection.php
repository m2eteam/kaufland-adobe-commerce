<?php

namespace M2E\Kaufland\Model\ResourceModel\OperationHistory;

/**
 * Class \M2E\Kaufland\Model\ResourceModel\OperationHistory\Collection
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init(
            \M2E\Kaufland\Model\OperationHistory::class,
            \M2E\Kaufland\Model\ResourceModel\OperationHistory::class
        );
    }

    //########################################
}
