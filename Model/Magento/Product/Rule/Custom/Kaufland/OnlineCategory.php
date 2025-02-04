<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland;

class OnlineCategory extends \M2E\Kaufland\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'kaufland_online_category';

    public function getLabel(): string
    {
        return (string)__('Category ID');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData(\M2E\Kaufland\Model\ResourceModel\Product::COLUMN_ONLINE_CATEGORY_ID);
    }
}
