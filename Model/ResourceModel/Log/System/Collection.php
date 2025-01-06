<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Log\System;

/**
 * @method \M2E\Kaufland\Model\Log\System getFirstItem()
 * @method \M2E\Kaufland\Model\Log\System[] getItems()
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Log\System::class,
            \M2E\Kaufland\Model\ResourceModel\Log\System::class
        );
    }
}
