<?php

namespace M2E\Kaufland\Observer\StockItem\Save;

/**
 * Class \M2E\Kaufland\Observer\StockItem\Save\Before
 */
class Before extends \M2E\Kaufland\Observer\StockItem\AbstractStockItem
{
    //########################################

    public function beforeProcess(): void
    {
        parent::beforeProcess();
        $this->clearStoredStockItem();
    }

    public function afterProcess(): void
    {
        parent::afterProcess();
        $this->storeStockItem();
    }

    // ---------------------------------------

    protected function process(): void
    {
        if ($this->isAddingStockItemProcess()) {
            return;
        }

        $this->reloadStockItem();
    }

    //########################################

    protected function isAddingStockItemProcess()
    {
        return (int)$this->stockItemId <= 0;
    }

    //########################################

    private function clearStoredStockItem()
    {
        if ($this->isAddingStockItemProcess()) {
            return;
        }

        $key = $this->getStockItemId() . '_' . $this->getStoreId();
        $this->registry->unregister($key);
    }

    private function storeStockItem()
    {
        if ($this->isAddingStockItemProcess()) {
            return;
        }

        $key = $this->getStockItemId() . '_' . $this->getStoreId();
        $this->getRegistry()->register($key, $this->getStockItem());
    }

    //########################################
}
