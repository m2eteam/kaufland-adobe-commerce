<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Product;

/**
 * @method \M2E\Kaufland\Model\Product getFirstItem()
 * @method \M2E\Kaufland\Model\Product[] getItems()
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Product::class,
            \M2E\Kaufland\Model\ResourceModel\Product::class
        );
    }
}
