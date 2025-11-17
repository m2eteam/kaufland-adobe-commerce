<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland;

class OnlineSku extends \M2E\Kaufland\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'kaufland_online_sku';

    public function getLabel(): string
    {
        return (string)__('SKU');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData('sku');
    }
}
