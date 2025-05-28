<?php

namespace M2E\Kaufland\Model\Channel\Magento\Product\Rule\Condition;

class Product extends \M2E\Kaufland\Model\Magento\Product\Rule\Condition\Product
{
    /**
     * @param mixed $validatedValue
     *
     * @return bool
     */
    public function validateAttribute($validatedValue): bool
    {
        if (is_array($validatedValue)) {
            $result = false;

            foreach ($validatedValue as $value) {
                $result = parent::validateAttribute($value);
                if ($result) {
                    break;
                }
            }

            return $result;
        }

        return parent::validateAttribute($validatedValue);
    }
}
