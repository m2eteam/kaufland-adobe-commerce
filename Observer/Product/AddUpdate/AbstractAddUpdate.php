<?php

namespace M2E\Kaufland\Observer\Product\AddUpdate;

abstract class AbstractAddUpdate extends \M2E\Kaufland\Observer\Product\AbstractProduct
{
    private array $affectedListingsProducts = [];
    private \M2E\Kaufland\Model\Product\Repository $listingProductRepository;

    public function __construct(
        \M2E\Kaufland\Model\Product\Repository $listingProductRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory
    ) {
        parent::__construct(
            $productFactory,
            $activeRecordFactory,
            $modelFactory
        );
        $this->listingProductRepository = $listingProductRepository;
    }

    /**
     * @return bool
     */
    public function canProcess(): bool
    {
        return ((string)$this->getEvent()->getProduct()->getSku()) !== '';
    }

    //########################################

    abstract protected function isAddingProductProcess();

    //########################################

    protected function areThereAffectedItems(): bool
    {
        return !empty($this->getAffectedListingsProducts());
    }

    // ---------------------------------------

    /**
     * @return \M2E\Kaufland\Model\Product[]
     */
    protected function getAffectedListingsProducts(): array
    {
        if (!empty($this->affectedListingsProducts)) {
            return $this->affectedListingsProducts;
        }

        return $this->affectedListingsProducts = $this->listingProductRepository
            ->getItemsByMagentoProductId($this->getProductId());
    }

    //########################################
}
