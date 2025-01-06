<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\ShippingGroup;

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(
            \M2E\Kaufland\Model\ShippingGroup::class,
            \M2E\Kaufland\Model\ResourceModel\ShippingGroup::class
        );
    }
}
