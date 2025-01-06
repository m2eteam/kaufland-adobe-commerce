<?php

declare(strict_types=1);

namespace M2E\Kaufland\Helper\Magento;

use M2E\Core\Helper\Magento\AbstractHelper;

class Attribute extends AbstractHelper
{
    private \M2E\Kaufland\Helper\Module\Configuration $moduleConfiguration;
    private \M2E\Kaufland\Model\Currency $currency;
    private \M2E\Core\Helper\Magento\Attribute $coreAttributeHelper;

    public function __construct(
        \M2E\Kaufland\Helper\Module\Configuration $moduleConfiguration,
        \M2E\Kaufland\Model\Currency $currency,
        \M2E\Core\Helper\Magento\Attribute $coreAttributeHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($objectManager);
        $this->moduleConfiguration = $moduleConfiguration;
        $this->currency = $currency;
        $this->coreAttributeHelper = $coreAttributeHelper;
    }

    public function convertAttributeTypePriceFromStoreToStorefront(
        \M2E\Kaufland\Model\Magento\Product $magentoProduct,
        $attributeCode,
        string $currencyCode,
        int $store
    ) {
        $attributeValue = $magentoProduct->getAttributeValue($attributeCode);
        if (empty($attributeValue)) {
            return $attributeValue;
        }

        $isPriceConvertEnabled = $this->moduleConfiguration->isEnableMagentoAttributePriceTypeConvertingMode();

        if ($isPriceConvertEnabled && $this->coreAttributeHelper->isAttributeInputTypePrice($attributeCode)) {
            $attributeValue = $this->currency->convertPrice(
                $attributeValue,
                $currencyCode,
                $store
            );
        }

        return $attributeValue;
    }
}
