<?php

namespace M2E\Kaufland\Observer\Product;

abstract class AbstractProduct extends \M2E\Kaufland\Observer\AbstractObserver
{
    protected \Magento\Catalog\Model\ProductFactory $productFactory;
    private ?\Magento\Catalog\Model\Product $product = null;
    private ?int $productId = null;
    private ?int $storeId = null;
    private ?\M2E\Kaufland\Model\Magento\Product $magentoProduct = null;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory
    ) {
        $this->productFactory = $productFactory;
        parent::__construct($activeRecordFactory, $modelFactory);
    }

    //########################################

    public function beforeProcess(): void
    {
        $product = $this->getEvent()->getProduct();

        if (!($product instanceof \Magento\Catalog\Model\Product)) {
            throw new \M2E\Kaufland\Model\Exception('Product event doesn\'t have correct Product instance.');
        }

        $this->product = $product;

        $this->productId = (int)$this->product->getId();
        $this->storeId = (int)$this->product->getData('store_id');
    }

    //########################################

    /**
     * @return \Magento\Catalog\Model\Product
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function getProduct()
    {
        if (!($this->product instanceof \Magento\Catalog\Model\Product)) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Property "Product" should be set first.');
        }

        return $this->product;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function reloadProduct()
    {
        if ($this->getProductId() <= 0) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                'To reload Product instance product_id should be
                greater than 0.'
            );
        }

        $this->product = $this->productFactory->create()
                                              ->setStoreId($this->getStoreId())
                                              ->load($this->getProductId());

        return $this->getProduct();
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getProductId()
    {
        return (int)$this->productId;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        return (int)$this->storeId;
    }

    //########################################

    /**
     * @return bool
     */
    protected function isAdminDefaultStoreId()
    {
        return $this->getStoreId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    /**
     * @return \M2E\Kaufland\Model\Magento\Product
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function getMagentoProduct()
    {
        if (!empty($this->magentoProduct)) {
            return $this->magentoProduct;
        }

        if ($this->getProductId() <= 0) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                'To load Magento Product instance product_id should be
                greater than 0.'
            );
        }

        return $this->magentoProduct = $this->modelFactory->getObject('Magento\Product')
                                                          ->setProduct($this->getProduct());
    }

    //########################################
}
