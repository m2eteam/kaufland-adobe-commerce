<?php

namespace M2E\Kaufland\Observer\StockItem;

abstract class AbstractStockItem extends \M2E\Kaufland\Observer\AbstractObserver
{
    /** @var \Magento\Framework\Registry */
    protected $registry;
    /** @var \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory */
    protected $stockItemFactory;
    /**
     * @var null|\Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    protected $stockItem = null;

    /**
     * @var null|int
     */
    protected $stockItemId = null;
    /**
     * @var null|int
     */
    protected $storeId = null;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory
    ) {
        $this->registry = $registry;
        $this->stockItemFactory = $stockItemFactory;
        parent::__construct($activeRecordFactory, $modelFactory);
    }

    //########################################

    public function beforeProcess(): void
    {
        $stockItem = $this->getEventObserver()->getData('item');

        if (!($stockItem instanceof \Magento\CatalogInventory\Api\Data\StockItemInterface)) {
            throw new \M2E\Kaufland\Model\Exception('StockItem event doesn\'t have correct StockItem instance.');
        }

        $this->stockItem = $stockItem;

        $this->stockItemId = (int)$this->stockItem->getId();
        $this->storeId = (int)$this->stockItem->getData('store_id');
    }

    //########################################

    /**
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function getStockItem()
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->stockItem)) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Property "StockItem" should be set first.');
        }

        return $this->stockItem;
    }

    /**
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function reloadStockItem()
    {
        if ($this->getStockItemId() <= 0) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                'To reload StockItem instance stockitem_id should be
                greater than 0.'
            );
        }

        $this->stockItem = $this->stockItemFactory->create()
                                                  ->setStoreId($this->getStoreId())
                                                  ->load($this->getStockItemId());

        return $this->getStockItem();
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getStockItemId()
    {
        return (int)$this->stockItemId;
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
     * @return \Magento\Framework\Registry
     */
    protected function getRegistry()
    {
        return $this->registry;
    }

    //########################################
}
