<?php

namespace M2E\Kaufland\Model\Kaufland\Listing\Product;

/**
 * @method \M2E\Kaufland\Model\Listing getComponentListing()
 * @method \M2E\Kaufland\Model\Template\SellingFormat getComponentSellingFormatTemplate()
 * @method \M2E\Kaufland\Model\Product getComponentProduct()
 */
class QtyCalculator extends \M2E\Kaufland\Model\Product\QtyCalculator
{
    /**
     * @var bool
     */
    private $isMagentoMode = false;

    //########################################

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

    public function getProductValue(): int
    {
        if ($this->getIsMagentoMode()) {
            return (int)$this->getMagentoProduct()->getQty(true);
        }

        return parent::getProductValue();
    }

    protected function applySellingFormatTemplateModifications($value)
    {
        if ($this->getIsMagentoMode()) {
            return $value;
        }

        return parent::applySellingFormatTemplateModifications($value);
    }
}
