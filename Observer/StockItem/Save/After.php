<?php

namespace M2E\Kaufland\Observer\StockItem\Save;

class After extends \M2E\Kaufland\Observer\StockItem\AbstractStockItem
{
    private \M2E\Kaufland\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private \M2E\Kaufland\Model\Listing\LogService $listingLogService;
    private ?int $productId = null;
    private array $affectedListingsParentProducts = [];
    private array $affectedListingsProducts = [];
    private \M2E\Kaufland\Model\Product\Repository $listingProductRepository;
    private \M2E\Kaufland\Model\ResourceModel\Product $listingProductResource;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Product                         $listingProductResource,
        \M2E\Kaufland\Model\Product\Repository                            $listingProductRepository,
        \M2E\Kaufland\Model\Listing\LogService                            $listingLogService,
        \Magento\Framework\Registry                                         $registry,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory        $stockItemFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory                          $activeRecordFactory,
        \M2E\Kaufland\Model\Factory                                       $modelFactory,
        \M2E\Kaufland\Model\Magento\Product\ChangeAttributeTrackerFactory $changeProcessorFactory
    ) {
        parent::__construct($registry, $stockItemFactory, $activeRecordFactory, $modelFactory);

        $this->changeAttributeTrackerFactory = $changeProcessorFactory;
        $this->listingLogService = $listingLogService;
        $this->listingProductRepository = $listingProductRepository;
        $this->listingProductResource = $listingProductResource;
    }

    public function beforeProcess(): void
    {
        parent::beforeProcess();

        $productId = (int)$this->getStockItem()->getProductId();

        if ($productId <= 0) {
            throw new \M2E\Kaufland\Model\Exception('Product ID should be greater than 0.');
        }

        $this->productId = $productId;

        $this->reloadStockItem();
    }

    protected function process(): void
    {
        if ($this->getStoredStockItem() === null) {
            return;
        }

        if (!$this->areThereAffectedItems()) {
            return;
        }

        $this->addListingProductInstructions();

        $this->processQty();
        $this->processStockAvailability();
    }

    protected function processQty()
    {
        $oldValue = (int)$this->getStoredStockItem()->getOrigData('qty');
        $newValue = (int)$this->getStockItem()->getQty();

        if ($oldValue == $newValue) {
            return;
        }

        $listingProducts = array_merge(
            $this->getAffectedListingsProducts(),
            $this->getAffectedListingsParentProducts()
        );

        foreach ($listingProducts as $listingProduct) {
            /** @var \M2E\Kaufland\Model\Product $listingProduct */

            $this->logListingProductMessage(
                $listingProduct,
                \M2E\Kaufland\Model\Listing\Log::ACTION_CHANGE_PRODUCT_QTY,
                $oldValue,
                $newValue
            );
        }
    }

    protected function processStockAvailability()
    {
        $oldValue = (bool)$this->getStoredStockItem()->getOrigData('is_in_stock');
        $newValue = (bool)$this->getStockItem()->getIsInStock();

        $oldValue = $oldValue ? 'IN Stock' : 'OUT of Stock';
        $newValue = $newValue ? 'IN Stock' : 'OUT of Stock';

        if ($oldValue == $newValue) {
            return;
        }

        $listingProducts = array_merge(
            $this->getAffectedListingsProducts(),
            $this->getAffectedListingsParentProducts()
        );

        foreach ($listingProducts as $listingProduct) {
            /** @var \M2E\Kaufland\Model\Product $listingProduct */

            $this->logListingProductMessage(
                $listingProduct,
                \M2E\Kaufland\Model\Listing\Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
                $oldValue,
                $newValue
            );
        }
    }

    protected function getProductId()
    {
        return $this->productId;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function addListingProductInstructions()
    {
        $listingProducts = array_merge(
            $this->getAffectedListingsProducts(),
            $this->getAffectedListingsParentProducts()
        );

        foreach ($listingProducts as $listingProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $listingProduct
            );
            $changeAttributeTracker->addInstructionWithPotentiallyChangedType();
            $changeAttributeTracker->flushInstructions();
        }
    }

    protected function areThereAffectedItems(): bool
    {
        return !empty($this->getAffectedListingsProducts())
            || !empty($this->getAffectedListingsParentProducts());
    }

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

    // ---------------------------------------

    private function getAffectedListingsParentProducts(): array
    {
        if (!empty($this->affectedListingsParentProducts)) {
            return $this->affectedListingsParentProducts;
        }

        $listingProduct = $this->listingProductResource;
        $parentIds = $listingProduct->getParentEntityIdsByChild($this->getProductId());

        $affectedListingsParentProducts = [];
        foreach ($parentIds as $id) {
            $listingsParentProducts = $this->listingProductRepository->getItemsByMagentoProductId($id);
            $affectedListingsParentProducts = array_merge($affectedListingsParentProducts, $listingsParentProducts);
        }

        return $this->affectedListingsParentProducts = $affectedListingsParentProducts;
    }

    protected function logListingProductMessage(
        \M2E\Kaufland\Model\Product $listingProduct,
        $action,
        $oldValue,
        $newValue
    ): void {
        $this->listingLogService->addProduct(
            $listingProduct,
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
            $action,
            null,
            \M2E\Kaufland\Helper\Module\Log::encodeDescription(
                'From [%from%] to [%to%].',
                ['!from' => $oldValue, '!to' => $newValue]
            ),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO
        );
    }

    private function getStoredStockItem()
    {
        $key = $this->getStockItemId() . '_' . $this->getStoreId();

        return $this->getRegistry()->registry($key);
    }
}
