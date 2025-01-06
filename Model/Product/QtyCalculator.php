<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

abstract class QtyCalculator extends \M2E\Kaufland\Model\AbstractModel
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

    public function __construct(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Helper\Module\Configuration $moduleConfiguration,
        array $data = []
    ) {
        parent::__construct($data);

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

    protected function getSellingFormatTemplate(): \M2E\Kaufland\Model\Template\SellingFormat
    {
        return $this->getProduct()->getSellingFormatTemplate();
    }

    // ---------------------------------------

    /**
     * @param null|string $key
     *
     * @return array|mixed
     */
    protected function getSource($key = null)
    {
        if ($this->source === null) {
            $this->source = $this->getSellingFormatTemplate()->getQtySource();
        }

        return ($key !== null && isset($this->source[$key])) ?
            $this->source[$key] : $this->source;
    }

    protected function getMagentoProduct(): \M2E\Kaufland\Model\Magento\Product\Cache
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

    protected function getClearProductValue()
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

    protected function applySellingFormatTemplateModifications($value)
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

    protected function applyValuePercentageModifications($value)
    {
        $percents = $this->getSource('qty_percentage');

        if ($value <= 0 || $percents < 0 || $percents == 100) {
            return $value;
        }

        $roundingFunction = $this->moduleConfiguration->getQtyPercentageRoundingGreater() ? 'ceil' : 'floor';

        return (int)$roundingFunction(($value / 100) * $percents);
    }

    protected function applyValueMinMaxModifications($value)
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
}
