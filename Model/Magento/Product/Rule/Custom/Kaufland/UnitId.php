<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland;

class UnitId extends \M2E\Kaufland\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'kaufland_unit_id';

    public function getLabel(): string
    {
        return (string)__('Unit ID');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData(\M2E\Kaufland\Model\ResourceModel\Product::COLUMN_UNIT_ID);
    }
}
