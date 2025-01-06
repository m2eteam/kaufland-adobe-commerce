<?php

namespace M2E\Kaufland\Model\Kaufland\Magento\Product;

/**
 * Class \M2E\Kaufland\Model\Kaufland\Magento\Product\Rule
 */
class Rule extends \M2E\Kaufland\Model\Magento\Product\Rule
{
    //########################################

    /**
     * @return string
     */
    public function getConditionClassName()
    {
        return 'Kaufland_Magento_Product_Rule_Condition_Combine';
    }

    //########################################
}
