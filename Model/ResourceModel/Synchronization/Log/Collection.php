<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Synchronization\Log;

/**
 * @method \M2E\Kaufland\Model\Synchronization\Log[] getItems()
 * @method \M2E\Kaufland\Model\Synchronization\Log getFirstItem()
 */

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Synchronization\Log::class,
            \M2E\Kaufland\Model\ResourceModel\Synchronization\Log::class
        );
    }
}
