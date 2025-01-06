<?php

namespace M2E\Kaufland\Observer\Order;

class Quote extends \M2E\Kaufland\Observer\AbstractObserver
{
    private \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory;
    private \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry;
    private ?\Magento\Catalog\Model\Product $product = null;
    private ?\Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem = null;
    private \M2E\Kaufland\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private \M2E\Kaufland\Model\Listing\LogService $listingLogService;
    private array $affectedListingsProducts = [];
    private \M2E\Kaufland\Model\Product\Repository $listingProductRepository;

    public function __construct(
        \M2E\Kaufland\Model\Product\Repository $listingProductRepository,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \M2E\Kaufland\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory
    ) {
        parent::__construct($activeRecordFactory, $modelFactory);

        $this->changeAttributeTrackerFactory = $changeAttributeTrackerFactory;
        $this->stockItemFactory = $stockItemFactory;
        $this->stockRegistry = $stockRegistry;
        $this->listingLogService = $listingLogService;
        $this->listingProductRepository = $listingProductRepository;
    }

    public function beforeProcess(): void
    {
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $this->getEvent()->getItem();

        $product = $quoteItem->getProduct();

        if (!($product instanceof \Magento\Catalog\Model\Product) || $product->getId() <= 0) {
            throw new \M2E\Kaufland\Model\Exception('Product ID should be greater than 0.');
        }

        $this->product = $product;
    }

    protected function process(): void
    {
        if (!$this->areThereAffectedItems()) {
            return;
        }

        $this->addListingProductInstructions();

        $this->processQty();
        $this->processStockAvailability();
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function processQty(): void
    {
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $this->getEvent()->getItem();

        if ($quoteItem->getHasChildren()) {
            return;
        }

        $oldValue = (int)$this->getStockItem()->getQty();
        $newValue = $oldValue - (int)$quoteItem->getTotalQty();

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            $this->logListingProductMessage(
                $listingProduct,
                \M2E\Kaufland\Model\Listing\Log::ACTION_CHANGE_PRODUCT_QTY,
                $oldValue,
                $newValue
            );
        }
    }

    private function processStockAvailability(): void
    {
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $this->getEvent()->getItem();

        if ($quoteItem->getHasChildren()) {
            return;
        }

        $oldQty = (int)$this->getStockItem()->getQty();
        $newQty = $oldQty - (int)$quoteItem->getTotalQty();

        $oldValue = (bool)$this->getStockItem()->getIsInStock();
        $newValue = !($newQty <= (int)$this->stockItemFactory->create()->getMinQty());

        $oldValue = $oldValue ? 'IN Stock' : 'OUT of Stock';
        $newValue = $newValue ? 'IN Stock' : 'OUT of Stock';

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            /** @var \M2E\Kaufland\Model\Product $listingProduct */

            $this->logListingProductMessage(
                $listingProduct,
                \M2E\Kaufland\Model\Listing\Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
                $oldValue,
                $newValue
            );
        }
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function getProduct(): \Magento\Catalog\Model\Product
    {
        if (!($this->product instanceof \Magento\Catalog\Model\Product)) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Property "Product" should be set first.');
        }

        return $this->product;
    }

    private function getStockItem(): \Magento\CatalogInventory\Api\Data\StockItemInterface
    {
        if ($this->stockItem !== null) {
            return $this->stockItem;
        }

        $stockItem = $this->stockRegistry->getStockItem(
            $this->getProduct()->getId(),
            $this->getProduct()->getStore()->getWebsiteId()
        );

        return $this->stockItem = $stockItem;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function addListingProductInstructions(): void
    {
        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $listingProduct,
            );
            $changeAttributeTracker->addInstructionWithPotentiallyChangedType();
            $changeAttributeTracker->flushInstructions();
        }
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function areThereAffectedItems(): bool
    {
        return !empty($this->getAffectedListingsProducts());
    }

    /**
     * @return \M2E\Kaufland\Model\Product[]
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function getAffectedListingsProducts(): array
    {
        if (!empty($this->affectedListingsProducts)) {
            return $this->affectedListingsProducts;
        }

        return $this->affectedListingsProducts = $this
            ->listingProductRepository
            ->getItemsByMagentoProductId($this->getProduct()->getId());
    }

    private function logListingProductMessage(
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
}
