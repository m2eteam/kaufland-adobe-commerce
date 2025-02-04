<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland;

class OnlineQty extends \M2E\Kaufland\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'kaufland_online_qty';

    public function getLabel(): string
    {
        return (string)__('Available QTY');
    }

    public function getInputType(): string
    {
        return \M2E\Kaufland\Model\Magento\Product\Rule\Condition\AbstractModel::INPUT_TYPE_NUMERIC;
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData(\M2E\Kaufland\Model\ResourceModel\Product::COLUMN_ONLINE_QTY);
    }
}
