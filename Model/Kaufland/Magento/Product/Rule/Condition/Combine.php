<?php

namespace M2E\Kaufland\Model\Kaufland\Magento\Product\Rule\Condition;

class Combine extends \M2E\Kaufland\Model\Magento\Product\Rule\Condition\Combine
{
    public function __construct(
        \M2E\Kaufland\Model\Magento\Product\Rule\Condition\ProductFactory $ruleConditionProductFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Rule\Model\Condition\Context $context,
        array $data = []
    ) {
        parent::__construct($ruleConditionProductFactory, $objectManager, $context, $data);
        $this->setType('Kaufland_Magento_Product_Rule_Condition_Combine');
    }

    /**
     * @return string
     */
    protected function getConditionCombine()
    {
        return $this->getType() . '|kaufland|';
    }

    /**
     * @return string
     */
    protected function getCustomLabel()
    {
        return (string)__('Kaufland Values');
    }

    /**
     * @return array
     */
    protected function getCustomOptions()
    {
        $attributes = $this->getCustomOptionsAttributes();

        return !empty($attributes) ?
            $this->getOptions('Kaufland_Magento_Product_Rule_Condition_Product', $attributes, ['kaufland'])
            : [];
    }
}
