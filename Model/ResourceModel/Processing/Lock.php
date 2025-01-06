<?php

namespace M2E\Kaufland\Model\ResourceModel\Processing;

class Lock extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        $this->_init(\M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING_LOCK, 'id');
    }
}
