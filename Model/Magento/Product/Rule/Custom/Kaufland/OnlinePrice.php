<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland;

class OnlinePrice extends \M2E\Kaufland\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'kaufland_online_price';

    public function getLabel(): string
    {
        return (string)__('Price');
    }

    public function getInputType(): string
    {
        return \M2E\Kaufland\Model\Magento\Product\Rule\Condition\AbstractModel::INPUT_TYPE_PRICE;
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData(\M2E\Kaufland\Model\ResourceModel\Product::COLUMN_ONLINE_PRICE);
    }
}
