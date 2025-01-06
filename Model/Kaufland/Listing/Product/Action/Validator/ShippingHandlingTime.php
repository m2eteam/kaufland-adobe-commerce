<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator;

use Magento\Catalog\Api\Data\ProductAttributeInterface;

class ShippingHandlingTime implements \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ValidatorInterface
{
    private \Magento\Eav\Model\Config $eavConfig;

    public function __construct(
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param \M2E\Kaufland\Model\Product $product
     *
     * @return string|null
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(\M2E\Kaufland\Model\Product $product): ?string
    {
        $shippingDataProvider = $product->getShippingPolicyDataProvider();
        if (
            $shippingDataProvider->isHandlingTimeModeAttribute()
            && empty($shippingDataProvider->getHandlingTime())
        ) {
            $attribute = $this->eavConfig->getAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $shippingDataProvider->getHandlingTimeAttribute()
            );

            return (string)__(
                'Handling Time is missing or invalid. Please ensure that the Attribute %attribute_title set for Handling Time has a valid value.',
                [
                    'attribute_title' => $attribute->getDefaultFrontendLabel(),
                ]
            );
        }

        if ($shippingDataProvider->getHandlingTime() > 100 || $shippingDataProvider->getHandlingTime() < 0) {
            return (string)__(
                'Handling Time must be positive whole number less than 100'
            );
        }

        return null;
    }
}
