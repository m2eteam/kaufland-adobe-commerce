<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Magento\Product\Rule\Condition;

class Combine extends \M2E\Kaufland\Model\Magento\Product\Rule\Condition\Combine
{
    private const CONDITION_SUFFIX = 'kaufland';

    /**
     * @return string
     */
    protected function getConditionCombine(): string
    {
        return $this->getType() . '|' . self::CONDITION_SUFFIX . '|';
    }

    /**
     * @return string
     */
    protected function getCustomLabel(): string
    {
        return \M2E\Kaufland\Helper\Module::getExtensionTitle() . ' Values';
    }

    protected function getCustomOptions(): array
    {
        $attributes = $this->getCustomOptionsAttributes();

        if (empty($attributes)) {
            return [];
        }

        return $this->getOptions(
            \M2E\Kaufland\Model\Channel\Magento\Product\Rule\Condition\Product::class,
            $attributes,
            [self::CONDITION_SUFFIX]
        );
    }

    protected function getCustomOptionsAttributes(): array
    {
        return [
            \M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland\OnlineCategory::NICK => __('Category ID'),
            \M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland\OnlineQty::NICK => __('Available QTY'),
            \M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland\OnlineSku::NICK => __('SKU'),
            \M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland\OnlineTitle::NICK => __('Title'),
            \M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland\Status::NICK => __('Status'),
            \M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland\UnitId::NICK => __('Unit ID'),
            \M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland\ProductId::NICK => __('Product ID'),
            \M2E\Kaufland\Model\Magento\Product\Rule\Custom\Kaufland\OnlinePrice::NICK => __('Price'),
        ];
    }
}
