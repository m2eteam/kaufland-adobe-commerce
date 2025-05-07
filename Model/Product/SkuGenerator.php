<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

class SkuGenerator
{
    private \M2E\Kaufland\Model\Product $product;
    private \M2E\Kaufland\Model\Listing\Settings\Sku $skuSettings;

    public function __construct(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Listing\Settings\Sku $skuSettings
    ) {
        $this->product = $product;
        $this->skuSettings = $skuSettings;
    }

    public function retrieveSku(): string
    {
        if ($this->skuSettings->isGenerateSkuModeYes()) {
            return \M2E\Core\Helper\Data::md5String(rand(0, 10000) . microtime(true));
        }

        $result = '';

        if ($this->skuSettings->isSkuDefaultMode()) {
            $result = $this->product->getMagentoProduct()->getSku();
        } elseif ($this->skuSettings->isSkuProductIdMode()) {
            $result = (string)$this->product->getMagentoProduct()->getProductId();
        } elseif ($this->skuSettings->isSkuAttributeMode()) {
            $result = $this->product->getMagentoProduct()->getAttributeValue($this->skuSettings->getSkuCustomAttribute());
        }

        $result = trim($result);
        if (!empty($result)) {
            $result = $this->applySkuModification($result);
        }

        return $result;
    }

    private function applySkuModification(string $sku): string
    {
        if ($this->skuSettings->isSkuModificationModeNone()) {
            return $sku;
        }

        if ($this->skuSettings->isSkuModificationModePrefix()) {
            $sku = $this->skuSettings->getSkuModificationCustomValue() . '_' . $sku;
        } elseif ($this->skuSettings->isSkuModificationModePostfix()) {
            $sku = $sku . '_' . $this->skuSettings->getSkuModificationCustomValue();
        }

        return $sku;
    }
}
