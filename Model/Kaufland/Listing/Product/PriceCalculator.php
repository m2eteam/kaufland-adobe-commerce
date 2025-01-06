<?php

namespace M2E\Kaufland\Model\Kaufland\Listing\Product;

class PriceCalculator extends \M2E\Kaufland\Model\Product\PriceCalculator
{
    protected function prepareOptionTitles($optionTitles)
    {
        foreach ($optionTitles as &$optionTitle) {
            $optionTitle = trim(
                \M2E\Core\Helper\Data::reduceWordsInString(
                    $optionTitle,
                    \M2E\Kaufland\Helper\Component\Kaufland::MAX_LENGTH_FOR_OPTION_VALUE
                )
            );
        }

        return $optionTitles;
    }

    protected function prepareAttributeTitles($attributeTitles)
    {
        foreach ($attributeTitles as &$attributeTitle) {
            $attributeTitle = trim($attributeTitle);
        }

        return $attributeTitles;
    }
}
