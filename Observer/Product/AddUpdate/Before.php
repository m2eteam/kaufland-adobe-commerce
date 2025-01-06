<?php

namespace M2E\Kaufland\Observer\Product\AddUpdate;

class Before extends AbstractAddUpdate
{
    public const BEFORE_EVENT_KEY = 'm2e_kaufland_before_event_key';

    private \M2E\Kaufland\Observer\Product\AddUpdate\Before\ProxyFactory $proxyFactory;
    private ?\M2E\Kaufland\Observer\Product\AddUpdate\Before\Proxy $proxy = null;
    public static array $proxyStorage = [];
    private \M2E\Kaufland\Helper\Magento\Product $helperMagentoProduct;

    public function __construct(
        \M2E\Kaufland\Helper\Magento\Product $helperMagentoProduct,
        \M2E\Kaufland\Model\Product\Repository $listingProductRepository,
        \M2E\Kaufland\Observer\Product\AddUpdate\Before\ProxyFactory $proxyFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory
    ) {
        parent::__construct(
            $listingProductRepository,
            $productFactory,
            $activeRecordFactory,
            $modelFactory
        );

        $this->proxyFactory = $proxyFactory;
        $this->helperMagentoProduct = $helperMagentoProduct;
    }

    public function beforeProcess(): void
    {
        parent::beforeProcess();
        $this->clearStoredProxy();
    }

    public function afterProcess(): void
    {
        parent::afterProcess();
        $this->storeProxy();
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function process(): void
    {
        if ($this->isAddingProductProcess()) {
            return;
        }

        $this->reloadProduct();

        $this->getProxy()->setData('name', $this->getProduct()->getName());

        $this->getProxy()->setWebsiteIds($this->getProduct()->getWebsiteIds());
        $this->getProxy()->setCategoriesIds($this->getProduct()->getCategoryIds());

        if (!$this->areThereAffectedItems()) {
            return;
        }

        $this->getProxy()->setData('status', (int)$this->getProduct()->getStatus());
        $this->getProxy()->setData('price', (float)$this->getProduct()->getPrice());
        $this->getProxy()->setData('special_price', (float)$this->getProduct()->getSpecialPrice());
        $this->getProxy()->setData('special_price_from_date', $this->getProduct()->getSpecialFromDate());
        $this->getProxy()->setData('special_price_to_date', $this->getProduct()->getSpecialToDate());
        $this->getProxy()->setData('tier_price', $this->getProduct()->getTierPrice());
        $this->getProxy()->setData('default_qty', $this->getDefaultQty());
    }

    protected function isAddingProductProcess()
    {
        return $this->getProductId() <= 0;
    }

    private function getProxy(): \M2E\Kaufland\Observer\Product\AddUpdate\Before\Proxy
    {
        if ($this->proxy !== null) {
            return $this->proxy;
        }

        $object = $this->proxyFactory->create();

        $object->setProductId($this->getProductId());
        $object->setStoreId($this->getStoreId());

        return $this->proxy = $object;
    }

    private function clearStoredProxy()
    {
        $key = $this->getProductId() . '_' . $this->getStoreId();
        if ($this->isAddingProductProcess()) {
            $key = $this->getProduct()->getSku();
        }

        unset(self::$proxyStorage[$key]);
    }

    private function storeProxy()
    {
        $key = $this->getProductId() . '_' . $this->getStoreId();
        if ($this->isAddingProductProcess()) {
            $key = \M2E\Core\Helper\Data::generateUniqueHash();
            $this->getEvent()->getProduct()->setData(self::BEFORE_EVENT_KEY, $key);
        }

        self::$proxyStorage[$key] = $this->getProxy();
    }

    protected function getDefaultQty()
    {
        if (!$this->helperMagentoProduct->isGroupedType($this->getProduct()->getTypeId())) {
            return [];
        }

        $values = [];
        foreach ($this->getProduct()->getTypeInstance()->getAssociatedProducts($this->getProduct()) as $childProduct) {
            $values[$childProduct->getSku()] = $childProduct->getQty();
        }

        return $values;
    }
}
