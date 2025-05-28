<?php

namespace M2E\Kaufland\Model\Product;

/**
 * @method \M2E\Kaufland\Model\Listing getComponentListing()
 * @method \M2E\Kaufland\Model\Template\SellingFormat getComponentSellingFormatTemplate()
 * @method \M2E\Kaufland\Model\Product getComponentProduct()
 */
class QtyCalculator
{
    /**
     * @var null|array
     */
    protected $source = null;

    private \M2E\Kaufland\Model\Product $product;

    /**
     * @var null|int
     */
    private $productValueCache = null;
    /** @var \M2E\Kaufland\Helper\Module\Configuration */
    private $moduleConfiguration;

    /**
     * @var bool
     */
    private $isMagentoMode = false;

    //########################################

    public function __construct(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Helper\Module\Configuration $moduleConfiguration
    ) {
        $this->moduleConfiguration = $moduleConfiguration;
        $this->product = $product;
    }

    /**
     * @return \M2E\Kaufland\Model\Product
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function getProduct(): \M2E\Kaufland\Model\Product
    {
        return $this->product;
    }

    protected function getListing(): \M2E\Kaufland\Model\Listing
    {
        return $this->getProduct()->getListing();
    }

    private function getSellingFormatTemplate(): \M2E\Kaufland\Model\Template\SellingFormat
    {
        return $this->getProduct()->getSellingFormatTemplate();
    }

    // ---------------------------------------

    /**
     * @param null|string $key
     *
     * @return array|mixed
     */
    private function getSource($key = null)
    {
        if ($this->source === null) {
            $this->source = $this->getSellingFormatTemplate()->getQtySource();
        }

        return ($key !== null && isset($this->source[$key])) ?
            $this->source[$key] : $this->source;
    }

    private function getMagentoProduct(): \M2E\Kaufland\Model\Magento\Product\Cache
    {
        return $this->getProduct()->getMagentoProduct();
    }

    // ----------------------------------------

    public function getProductValue(): int
    {
        if ($this->productValueCache !== null) {
            return $this->productValueCache;
        }

        $value = $this->getClearProductValue();

        $value = $this->applySellingFormatTemplateModifications($value);
        $value < 0 && $value = 0;

        return $this->productValueCache = (int)floor($value);
    }

    private function getClearProductValue()
    {
        switch ($this->getSource('mode')) {
            case \M2E\Kaufland\Model\Template\SellingFormat::QTY_MODE_NUMBER:
                $value = (int)$this->getSource('value');
                break;

            case \M2E\Kaufland\Model\Template\SellingFormat::QTY_MODE_ATTRIBUTE:
                $value = (int)$this->getMagentoProduct()->getAttributeValue($this->getSource('attribute'));
                break;

            case \M2E\Kaufland\Model\Template\SellingFormat::QTY_MODE_PRODUCT_FIXED:
                $value = (int)$this->getMagentoProduct()->getQty(false);
                break;

            case \M2E\Kaufland\Model\Template\SellingFormat::QTY_MODE_PRODUCT:
                $value = (int)$this->getMagentoProduct()->getQty(true);
                break;

            default:
                throw new \M2E\Kaufland\Model\Exception\Logic('Unknown Mode in Database.');
        }

        return $value;
    }

    private function applySellingFormatTemplateModifications($value)
    {
        $mode = $this->getSource('mode');

        if (
            $mode != \M2E\Kaufland\Model\Template\SellingFormat::QTY_MODE_ATTRIBUTE &&
            $mode != \M2E\Kaufland\Model\Template\SellingFormat::QTY_MODE_PRODUCT_FIXED &&
            $mode != \M2E\Kaufland\Model\Template\SellingFormat::QTY_MODE_PRODUCT
        ) {
            return $value;
        }

        $value = $this->applyValuePercentageModifications($value);
        $value = $this->applyValueMinMaxModifications($value);

        return $value;
    }

    // ---------------------------------------

    private function applyValuePercentageModifications($value)
    {
        $percents = $this->getSource('qty_percentage');

        if ($value <= 0 || $percents < 0 || $percents == 100) {
            return $value;
        }

        $roundingFunction = $this->moduleConfiguration->getQtyPercentageRoundingGreater() ? 'ceil' : 'floor';

        return (int)$roundingFunction(($value / 100) * $percents);
    }

    private function applyValueMinMaxModifications($value)
    {
        if ($value <= 0 || !$this->getSource('qty_modification_mode')) {
            return $value;
        }

        $minValue = $this->getSource('qty_min_posted_value');
        $value < $minValue && $value = 0;

        $maxValue = $this->getSource('qty_max_posted_value');
        $value > $maxValue && $value = $maxValue;

        return $value;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setIsMagentoMode($value)
    {
        $this->isMagentoMode = (bool)$value;

        return $this;
    }

    /**
     * @return bool
     */
    protected function getIsMagentoMode()
    {
        return $this->isMagentoMode;
    }
}
