<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\SellingFormat;

class Source
{
    private ?\M2E\Kaufland\Model\Magento\Product $magentoProduct = null;
    private ?\M2E\Kaufland\Model\Template\SellingFormat $sellingTemplateModel = null;

    public function setMagentoProduct(\M2E\Kaufland\Model\Magento\Product $magentoProduct): self
    {
        $this->magentoProduct = $magentoProduct;

        return $this;
    }

    public function getMagentoProduct(): ?\M2E\Kaufland\Model\Magento\Product
    {
        return $this->magentoProduct;
    }

    public function setSellingFormatTemplate(\M2E\Kaufland\Model\Template\SellingFormat $instance): self
    {
        $this->sellingTemplateModel = $instance;

        return $this;
    }

    public function getSellingFormatTemplate(): ?\M2E\Kaufland\Model\Template\SellingFormat
    {
        return $this->sellingTemplateModel;
    }

    public function isCashOnDeliveryEnabled(): bool
    {
        return $this->getSellingFormatTemplate()->isCashOnDeliveryEnabled();
    }
}
