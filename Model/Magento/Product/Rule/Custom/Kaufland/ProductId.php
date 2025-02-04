<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland;

class ProductId extends \M2E\Kaufland\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'kaufland_product_id';

    public function getLabel(): string
    {
        return (string)__('Product ID');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData(\M2E\Kaufland\Model\ResourceModel\Product::COLUMN_KAUFLAND_PRODUCT_ID);
    }
}
