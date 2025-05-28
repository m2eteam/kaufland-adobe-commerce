<?php

namespace M2E\Kaufland\Model\Product\Action;

class RequestData extends \Magento\Framework\DataObject
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

    /**
     * @return bool
     */
    public function hasQty()
    {
        return isset($this->getData()['qty']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasPrice()
    {
        return $this->hasPriceFixed() ||
            $this->hasPriceStart() ||
            $this->hasPriceReserve();
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasPriceFixed()
    {
        return isset($this->getData()['price_fixed']);
    }

    /**
     * @return bool
     */
    public function hasPriceStart()
    {
        return isset($this->getData()['price_start']);
    }

    /**
     * @return bool
     */
    public function hasPriceReserve()
    {
        return isset($this->getData()['price_reserve']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasSku()
    {
        return isset($this->getData()['sku']);
    }

    /**
     * @return bool
     */
    public function hasPrimaryCategory()
    {
        return isset($this->getData()['category_main_id']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasTitle()
    {
        return isset($this->getData()['title']);
    }

    /**
     * @return bool
     */
    public function hasSubtitle()
    {
        return isset($this->getData()['subtitle']);
    }

    /**
     * @return bool
     */
    public function hasDescription()
    {
        return isset($this->getData()['description']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasDuration()
    {
        return isset($this->getData()['duration']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasImages()
    {
        return isset($this->getData()['images']);
    }

    //########################################

    public function getQty()
    {
        return $this->hasQty() ? $this->getData()['qty'] : null;
    }

    // ---------------------------------------

    public function getPriceFixed()
    {
        return $this->hasPriceFixed() ? $this->getData()['price_fixed'] : null;
    }

    public function getPriceStart()
    {
        return $this->hasPriceStart() ? $this->getData()['price_start'] : null;
    }

    public function getPriceReserve()
    {
        return $this->hasPriceReserve() ? $this->getData()['price_reserve'] : null;
    }

    // ---------------------------------------

    public function getSku()
    {
        return $this->hasSku() ? $this->getData()['sku'] : null;
    }

    public function getPrimaryCategory()
    {
        return $this->hasPrimaryCategory() ? $this->getData()['category_main_id'] : null;
    }

    // ---------------------------------------

    public function getTitle()
    {
        return $this->hasTitle() ? $this->getData()['title'] : null;
    }

    public function getSubtitle()
    {
        return $this->hasSubtitle() ? $this->getData()['subtitle'] : null;
    }

    public function getDescription()
    {
        return $this->hasDescription() ? $this->getData()['description'] : null;
    }

    // ---------------------------------------

    public function getDuration()
    {
        return $this->hasDuration() ? $this->getData()['duration'] : null;
    }

    // ---------------------------------------

    public function getImages()
    {
        return $this->hasImages() ? $this->getData()['images'] : null;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getImagesCount()
    {
        if (!$this->hasImages()) {
            return 0;
        }

        $images = $this->getImages();
        $images = isset($images['images']) ? $images['images'] : [];

        return count($images);
    }
}
