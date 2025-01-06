<?php

namespace M2E\Kaufland\Model\Kaufland\Magento\Product\Rule\Custom;

class CategoryId extends \M2E\Kaufland\Model\Magento\Product\Rule\Custom\AbstractModel
{
    /**
     * @return string
     */
    public function getAttributeCode(): string
    {
        return 'online_category';
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return (string)__('Category ID');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        $onlineCategory = $product->getData('main_category');
        if (empty($onlineCategory)) {
            return null;
        }

        preg_match('/^.+\((\d+)\)$/x', $onlineCategory, $matches);

        if (empty($matches[1])) {
            return null;
        }

        return $matches[1];
    }
}
