<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland;

class OnlineTitle extends \M2E\Kaufland\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'kaufland_online_title';

    public function getLabel(): string
    {
        return (string)__('Title');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData(\M2E\Kaufland\Model\ResourceModel\Product::COLUMN_ONLINE_TITLE);
    }
}
