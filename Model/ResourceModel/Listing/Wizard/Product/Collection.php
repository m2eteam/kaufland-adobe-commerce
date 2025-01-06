<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product;

/**
 * @method \M2E\Kaufland\Model\Listing\Wizard\Product[] getItems()
 * @method \M2E\Kaufland\Model\Listing\Wizard\Product getFirstItem()
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Listing\Wizard\Product::class,
            \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product::class
        );
    }
}
