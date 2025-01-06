<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

use M2E\Core\Helper\Magento\Attribute;
use M2E\Kaufland\Model\Exception\Logic;
use M2E\Kaufland\Model\Listing;
use M2E\Kaufland\Model\Magento\Product;
use M2E\Kaufland\Model\Magento\Product\Cache;
use M2E\Kaufland\Model\Product as ListingProduct;
use M2E\Kaufland\Model\Template\SellingFormat;

abstract class PriceCalculator
{
    public const MODE_NONE = 0;
    public const MODE_PRODUCT = 1;
    public const MODE_SPECIAL = 2;
    public const MODE_ATTRIBUTE = 3;
    public const MODE_TIER = 4;

    /**
     * @var null|array
     */
    private ?array $source = null;

    /**
     * @var array
     */
    private $sourceModeMapping = [
        self::MODE_NONE => \M2E\Kaufland\Model\Template\SellingFormat::PRICE_MODE_NONE,
        self::MODE_PRODUCT => \M2E\Kaufland\Model\Template\SellingFormat::PRICE_MODE_PRODUCT,
        self::MODE_SPECIAL => \M2E\Kaufland\Model\Template\SellingFormat::PRICE_MODE_SPECIAL,
        self::MODE_ATTRIBUTE => \M2E\Kaufland\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE,
        self::MODE_TIER => \M2E\Kaufland\Model\Template\SellingFormat::PRICE_MODE_TIER,
    ];

    /**
     * @var null|ListingProduct
     */
    private $product = null;

    /** @var null|string */
    private $coefficient = null;
    /** @var array */
    private $modifier = [];
    /** @var \M2E\Kaufland\Model\Magento\Product\Cache|null */
    private $attributeSourceProduct;

    /**
     * @var null|float
     */
    private $vatPercent = null;

    /**
     * @var null|float
     */
    private $productValueCache = null;
    private \M2E\Kaufland\Model\Magento\ProductFactory $ourMagentoProductFactory;
    private \M2E\Kaufland\Model\Currency $currency;
    private \M2E\Kaufland\Helper\Magento\Attribute $attributeHelper;

    public function __construct(
        \M2E\Kaufland\Model\Currency $currency,
        \M2E\Kaufland\Model\Magento\ProductFactory $ourMagentorProductFactory,
        \M2E\Kaufland\Helper\Magento\Attribute $attributeHelper
    ) {
        $this->ourMagentoProductFactory = $ourMagentorProductFactory;
        $this->attributeHelper = $attributeHelper;
        $this->currency = $currency;
    }

    /**
     * @param array $source
     *
     * @return PriceCalculator
     */
    public function setSource(array $source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @param null|string $key
     *
     * @return array|mixed
     * @throws Logic
     */
    protected function getSource($key = null)
    {
        if (empty($this->source)) {
            throw new Logic('Initialize all parameters first.');
        }

        if ($key === null) {
            return $this->source;
        }

        return isset($this->source[$key]) ? $this->source[$key] : null;
    }

    // ---------------------------------------

    public function setSourceModeMapping(array $mapping)
    {
        $this->sourceModeMapping = $mapping;

        return $this;
    }

    protected function getSourceMode()
    {
        if (!in_array($this->getSource('mode'), $this->sourceModeMapping)) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Unknown source mode.');
        }

        return array_search($this->getSource('mode'), $this->sourceModeMapping);
    }

    // ---------------------------------------

    /**
     * @param ListingProduct $product
     *
     * @return PriceCalculator
     */
    public function setProduct(ListingProduct $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return ListingProduct
     * @throws Logic
     */
    protected function getProduct(): ListingProduct
    {
        if ($this->product === null) {
            throw new Logic('Initialize all parameters first.');
        }

        return $this->product;
    }

    // ---------------------------------------

    /**
     * @param string $value
     *
     * @return PriceCalculator
     */
    public function setCoefficient($value)
    {
        $this->coefficient = $value;

        return $this;
    }

    /**
     * @return string
     */
    protected function getCoefficient()
    {
        return $this->coefficient;
    }

    /**
     * @param array $value
     *
     * @return PriceCalculator
     */
    public function setModifier(array $value): PriceCalculator
    {
        $this->modifier = $value;

        return $this;
    }

    /**
     * @return array
     */
    protected function getModifier(): array
    {
        return $this->modifier;
    }

    /**
     * @param \M2E\Kaufland\Model\Magento\Product\Cache|null $attributeSourceProduct
     *
     * @return \M2E\Kaufland\Model\Product\PriceCalculator
     */
    public function setAttributeSourceProduct(?Cache $attributeSourceProduct): PriceCalculator
    {
        $this->attributeSourceProduct = $attributeSourceProduct;

        return $this;
    }

    /**
     * @return \M2E\Kaufland\Model\Magento\Product\Cache|null
     */
    public function getAttributeSourceProduct(): ?Cache
    {
        return $this->attributeSourceProduct;
    }

    // ---------------------------------------

    public function setVatPercent($value)
    {
        $this->vatPercent = $value;

        return $this;
    }

    /**
     * @return float|null
     */
    protected function getVatPercent()
    {
        return $this->vatPercent;
    }

    //########################################

    protected function getListing(): Listing
    {
        return $this->getProduct()->getListing();
    }

    /**
     * @return Cache
     */
    protected function getMagentoProduct()
    {
        return $this->getProduct()->getMagentoProduct();
    }

    //########################################

    public function getProductValue()
    {
        if ($this->isSourceModeNone()) {
            return 0;
        }

        $this->setAttributeSourceProduct($this->getMagentoProduct());
        $value = $this->getProductBaseValue();

        return $this->prepareFinalValue($value);
    }

    protected function getProductBaseValue()
    {
        if ($this->productValueCache !== null) {
            return $this->productValueCache;
        }

        if ($this->isSourceModeProduct()) {
            if ($this->getMagentoProduct()->isConfigurableType()) {
                $value = $this->getConfigurableProductValue($this->getMagentoProduct());
            } elseif ($this->getMagentoProduct()->isGroupedType()) {
                $value = $this->getGroupedProductValue($this->getMagentoProduct());
            } elseif (
                $this->getMagentoProduct()->isBundleType()
                && $this->getMagentoProduct()->isBundlePriceTypeDynamic()
            ) {
                $value = $this->getBundleProductDynamicValue($this->getMagentoProduct());
            } else {
                $value = $this->convertValueFromStoreToStorefront($this->getMagentoProduct()->getPrice());
            }
        } elseif ($this->isSourceModeSpecial()) {
            if ($this->getMagentoProduct()->isConfigurableType()) {
                $value = $this->getConfigurableProductValue($this->getMagentoProduct());
            } elseif ($this->getMagentoProduct()->isGroupedType()) {
                $value = $this->getGroupedProductValue($this->getMagentoProduct());
            } elseif (
                $this->getMagentoProduct()->isBundleType()
                && $this->getMagentoProduct()->isBundlePriceTypeDynamic()
            ) {
                $value = $this->getBundleProductDynamicSpecialValue($this->getMagentoProduct());
            } else {
                $value = $this->getExistedProductSpecialValue($this->getMagentoProduct());
            }
        } elseif ($this->isSourceModeAttribute()) {
            if ($this->getMagentoProduct()->isConfigurableType()) {
                if (
                    $this->getSource('attribute') == Attribute::PRICE_CODE
                    || $this->getSource('attribute') == Attribute::SPECIAL_PRICE_CODE
                ) {
                    $value = $this->getConfigurableProductValue($this->getMagentoProduct());
                } else {
                    $value = $this->attributeHelper->convertAttributeTypePriceFromStoreToStorefront(
                        $this->getMagentoProduct(),
                        $this->getSource('attribute'),
                        $this->getCurrencyForPriceConvert(),
                        $this->getListing()->getStoreId()
                    );
                }
            } elseif ($this->getMagentoProduct()->isGroupedType()) {
                if (
                    $this->getSource('attribute') == Attribute::PRICE_CODE
                    || $this->getSource('attribute') == Attribute::SPECIAL_PRICE_CODE
                ) {
                    $value = $this->getGroupedProductValue($this->getMagentoProduct());
                } else {
                    $value = $this->attributeHelper->convertAttributeTypePriceFromStoreToStorefront(
                        $this->getMagentoProduct(),
                        $this->getSource('attribute'),
                        $this->getCurrencyForPriceConvert(),
                        $this->getListing()->getStoreId()
                    );
                }
            } elseif (
                $this->getMagentoProduct()->isBundleType()
                && (
                    $this->getMagentoProduct()->isBundlePriceTypeDynamic()
                    || (
                        $this->getMagentoProduct()->isBundlePriceTypeFixed()
                        && $this->getSource('attribute') == Attribute::SPECIAL_PRICE_CODE
                    )
                )
            ) {
                if (
                    $this->getMagentoProduct()->isBundlePriceTypeFixed()
                    && $this->getSource('attribute') == Attribute::SPECIAL_PRICE_CODE
                ) {
                    $value = $this->getExistedProductSpecialValue($this->getMagentoProduct());
                } else {
                    if ($this->getSource('attribute') == Attribute::PRICE_CODE) {
                        $value = $this->getBundleProductDynamicValue($this->getMagentoProduct());
                    } elseif ($this->getSource('attribute') == Attribute::SPECIAL_PRICE_CODE) {
                        $value = $this->getBundleProductDynamicSpecialValue($this->getMagentoProduct());
                    } else {
                        $value = $this->attributeHelper->convertAttributeTypePriceFromStoreToStorefront(
                            $this->getMagentoProduct(),
                            $this->getSource('attribute'),
                            $this->getCurrencyForPriceConvert(),
                            $this->getListing()->getStoreId()
                        );
                    }
                }
            } else {
                $value = $this->attributeHelper->convertAttributeTypePriceFromStoreToStorefront(
                    $this->getMagentoProduct(),
                    $this->getSource('attribute'),
                    $this->getCurrencyForPriceConvert(),
                    $this->getListing()->getStoreId()
                );
            }
        } elseif ($this->isSourceModeTier()) {
            if ($this->getMagentoProduct()->isGroupedType()) {
                $value = $this->getGroupedTierValue($this->getMagentoProduct());
            } elseif ($this->getMagentoProduct()->isBundleType()) {
                if ($this->getMagentoProduct()->isBundlePriceTypeDynamic()) {
                    $value = $this->getBundleTierDynamicValue($this->getMagentoProduct());
                } else {
                    $value = $this->getBundleTierFixedValue($this->getMagentoProduct());
                }
            } else {
                $value = $this->getExistedProductTierValue($this->getMagentoProduct());
            }
        } else {
            throw new Logic('Unknown Mode in Database.');
        }

        return $this->productValueCache = !is_array($value) ? (float)$value : $value;
    }

    protected function getExistedProductSpecialValue(Product $product)
    {
        $value = (float)$product->getSpecialPrice();

        if ($value <= 0) {
            return $this->convertValueFromStoreToStorefront($product->getPrice());
        }

        return $this->convertValueFromStoreToStorefront($value);
    }

    protected function getExistedProductTierValue(Product $product)
    {
        $tierPrice = $product->getTierPrice(
            $this->getSource('tier_website_id'),
            $this->getSource('tier_customer_group_id'),
        );

        foreach ($tierPrice as $qty => $value) {
            $tierPrice[$qty] = $this->convertValueFromStoreToStorefront($value);
        }

        return $tierPrice;
    }

    // ---------------------------------------

    protected function getConfigurableProductValue(Product $product)
    {
        $value = 0;

        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();

        /** @var \Magento\Catalog\Model\Product $childProduct */
        foreach ($productTypeInstance->getUsedProducts($product->getProduct()) as $childProduct) {
            $childProduct = $this->ourMagentoProductFactory->create()->setProduct($childProduct);

            $variationValue = (float)$childProduct->getSpecialPrice();
            $variationValue <= 0 && $variationValue = (float)$childProduct->getPrice();

            if ($variationValue < $value || $value == 0) {
                $value = $variationValue;
            }
        }

        return $value;
    }

    /**
     * @param \M2E\Kaufland\Model\Magento\Product $product
     *
     * @return double
     * @throws \M2E\Kaufland\Model\Exception
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function getGroupedProductValue(Product $product)
    {
        $value = 0;
        foreach ($product->getTypeInstance()->getAssociatedProducts($product->getProduct()) as $childProduct) {
            /** @var \Magento\Catalog\Model\Product $childProduct */
            $variationValue = (float)$childProduct->getSpecialPrice();
            $variationValue <= 0 && $variationValue = (float)$childProduct->getPrice();
            $value = $variationValue;
        }

        return $value;
    }

    protected function getBundleProductDynamicValue(Product $product)
    {
        $value = 0;

        $variationsData = $product->getVariationInstance()->getVariationsTypeStandard();

        foreach ($variationsData['variations'] as $variation) {
            $variationValue = 0;

            foreach ($variation as $option) {
                /** @var Product $childProduct */
                $childProduct = $this->ourMagentoProductFactory->create()->setProductId($option['product_id']);

                $optionValue = (float)$childProduct->getSpecialPrice();
                $optionValue <= 0 && $optionValue = (float)$childProduct->getPrice();

                $variationValue += $optionValue;
            }

            if ($variationValue < $value || $value == 0) {
                $value = $variationValue;
            }
        }

        return $value;
    }

    protected function getBundleProductDynamicSpecialValue(Product $product)
    {
        $value = $this->getBundleProductDynamicValue($product);

        if ($value <= 0 || !$product->isSpecialPriceActual()) {
            return $value;
        }

        $percent = (float)$product->getProduct()->getSpecialPrice();

        return round((($value * $percent) / 100), 2);
    }

    protected function getGroupedTierValue(\M2E\Kaufland\Model\Magento\Product $product)
    {
        /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();

        $lowestVariationValue = null;
        $resultChildProduct = null;

        /** @var \Magento\Catalog\Model\Product $childProduct */
        foreach ($productTypeInstance->getAssociatedProducts($product->getProduct()) as $childProduct) {
            $childProduct = $this->ourMagentoProductFactory->create()->setProduct($childProduct);

            $variationValue = (float)$childProduct->getSpecialPrice();
            $variationValue <= 0 && $variationValue = (float)$childProduct->getPrice();

            if ($variationValue < $lowestVariationValue || $lowestVariationValue === null) {
                $lowestVariationValue = $variationValue;
                $resultChildProduct = $childProduct;
            }
        }

        if ($resultChildProduct === null) {
            return null;
        }

        return $this->getExistedProductTierValue($resultChildProduct);
    }

    protected function getBundleTierFixedValue(\M2E\Kaufland\Model\Magento\Product $product)
    {
        return $this->calculateBundleTierValue($product, $product->getPrice());
    }

    protected function getBundleTierDynamicValue(\M2E\Kaufland\Model\Magento\Product $product)
    {
        return $this->calculateBundleTierValue($product, $this->getBundleProductDynamicValue($product));
    }

    //########################################

    protected function prepareFinalValue($value)
    {
        if ($this->getCoefficient() !== null) {
            if (!$this->isSourceModeTier()) {
                $value = $this->modifyValueByCoefficient($value);
            } else {
                foreach ($value as $qty => $price) {
                    $value[$qty] = $this->modifyValueByCoefficient($price);
                }
            }
        }

        if (!empty($this->getModifier())) {
            if (!$this->isSourceModeTier()) {
                $value = $this->modifyValueByModifier($value);
            } else {
                foreach ($value as $qty => $price) {
                    $value[$qty] = $this->modifyValueByModifier($price);
                }
            }
        }

        if ($this->getVatPercent() !== null) {
            if (!$this->isSourceModeTier()) {
                $value = $this->increaseValueByVatPercent($value);
            } else {
                foreach ($value as $qty => $price) {
                    $value[$qty] = $this->increaseValueByVatPercent($price);
                }
            }
        }

        if (!$this->isSourceModeTier()) {
            $value < 0 && $value = 0;
            $value = round($value, 2);
        } else {
            foreach ($value as $qty => $price) {
                $price < 0 && $value[$qty] = 0;
                $value[$qty] = round($value[$qty], 2);
            }
        }

        return $value;
    }

    // ---------------------------------------

    protected function modifyValueByCoefficient($value)
    {
        if ($value <= 0) {
            return $value;
        }

        $coefficient = $this->getCoefficient();

        if (is_string($coefficient)) {
            $coefficient = trim($coefficient);
        }

        if (!$coefficient) {
            return $value;
        }

        if (strpos($coefficient, '%') !== false) {
            $coefficient = str_replace('%', '', $coefficient);

            if (preg_match('/^[+-]/', $coefficient)) {
                return $value + $value * (float)$coefficient / 100;
            }

            return $value * (float)$coefficient / 100;
        }

        if (preg_match('/^[+-]/', $coefficient)) {
            return $value + (float)$coefficient;
        }

        return $value * (float)$coefficient;
    }

    /**
     * @param $value
     *
     * @return float
     */
    protected function modifyValueByModifier($value)
    {
        if ($value <= 0) {
            return $value;
        }

        $result = $value;
        $modifier = $this->getModifier();
        $magentoProduct = $this->getAttributeSourceProduct();
        foreach ($modifier as $modification) {
            switch ($modification['mode']) {
                case SellingFormat::PRICE_MODIFIER_ABSOLUTE_INCREASE:
                    $result += (float)$modification['value'];
                    break;
                case SellingFormat::PRICE_MODIFIER_ABSOLUTE_DECREASE:
                    $result -= (float)$modification['value'];
                    break;
                case SellingFormat::PRICE_MODIFIER_PERCENTAGE_INCREASE:
                    $result *= 1 + (float)$modification['value'] / 100;
                    break;
                case SellingFormat::PRICE_MODIFIER_PERCENTAGE_DECREASE:
                    $result *= 1 - (float)$modification['value'] / 100;
                    break;
                case SellingFormat::PRICE_MODIFIER_ATTRIBUTE:
                    if (!$magentoProduct) {
                        break;
                    }

                    $attributeValue = $magentoProduct
                        ->getAttributeValue($modification['attribute_code']);
                    if (is_numeric($attributeValue)) {
                        $result += (float)$attributeValue;
                    }
            }
        }

        return $result;
    }

    protected function increaseValueByVatPercent($value)
    {
        return $value + (($this->getVatPercent() * $value) / 100);
    }

    private function convertValueFromStoreToStorefront($value)
    {
        return $this->currency->convertPrice(
            $value,
            $this->getCurrencyForPriceConvert(),
            $this->getListing()->getStoreId()
        );
    }

    private function getCurrencyForPriceConvert()
    {
        return $this->getListing()->getStorefront()->getCurrencyCode();
    }

    // ---------------------------------------

    protected function calculateBundleTierValue(Product $product, $baseValue)
    {
        $tierPrice = $product->getTierPrice(
            $this->getSource('tier_website_id'),
            $this->getSource('tier_customer_group_id'),
        );

        $value = [];

        foreach ($tierPrice as $qty => $discount) {
            $value[$qty] = round(($baseValue - ($baseValue * (float)$discount) / 100), 2);
        }

        return $value;
    }

    // ---------------------------------------

    protected function prepareOptionTitles($optionTitles)
    {
        return $optionTitles;
    }

    protected function prepareAttributeTitles($attributeTitles)
    {
        return $attributeTitles;
    }

    //########################################

    protected function isSourceModeNone()
    {
        return $this->getSourceMode() == self::MODE_NONE;
    }

    protected function isSourceModeProduct()
    {
        return $this->getSourceMode() == self::MODE_PRODUCT;
    }

    protected function isSourceModeSpecial()
    {
        return $this->getSourceMode() == self::MODE_SPECIAL;
    }

    protected function isSourceModeAttribute()
    {
        return $this->getSourceMode() == self::MODE_ATTRIBUTE;
    }

    protected function isSourceModeTier()
    {
        return $this->getSourceMode() == self::MODE_TIER;
    }
}
