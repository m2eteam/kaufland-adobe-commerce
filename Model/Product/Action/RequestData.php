<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action;

abstract class RequestData extends \Magento\Framework\DataObject
{
    /**
     * @var \M2E\Kaufland\Model\Product
     */
    protected $listingProduct = null;

    /**
     * @param \M2E\Kaufland\Model\Product $object
     */
    public function setListingProduct(\M2E\Kaufland\Model\Product $object)
    {
        $this->listingProduct = $object;
    }

    /**
     * @return \M2E\Kaufland\Model\Product
     */
    public function getListingProduct()
    {
        return $this->listingProduct;
    }
}
