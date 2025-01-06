<?php

namespace M2E\Kaufland\Model\Order;

use M2E\Kaufland\Model\Order\Exception\ProductCreationDisabled;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;

class Reserve
{
    public const STATE_UNKNOWN = 0;
    public const STATE_PLACED = 1;
    public const STATE_RELEASED = 2;
    public const STATE_CANCELED = 3;

    public const MAGENTO_RESERVATION_PLACED_EVENT_TYPE = 'Kaufland_reservation_placed';
    public const MAGENTO_RESERVATION_RELEASED_EVENT_TYPE = 'Kaufland_reservation_released';
    public const MAGENTO_RESERVATION_OBJECT_TYPE = 'Kaufland_order';

    public const ACTION_ADD = 'add';
    public const ACTION_SUB = 'sub';

    /** @var \Magento\Framework\DB\TransactionFactory */
    private $transactionFactory;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var \M2E\Kaufland\Model\Order */
    private $order;

    /** @var array */
    private $flags = [];

    private $qtyChangeInfo = [];
    private \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\Core\Helper\Magento $magentoHelper;
    private \M2E\Kaufland\Model\Magento\ProductFactory $magentoProductFactory;
    private \M2E\Kaufland\Model\Magento\Product\StockItemFactory $magentoProductStockItemFactory;

    public function __construct(
        \M2E\Kaufland\Model\Magento\ProductFactory $magentoProductFactory,
        \M2E\Kaufland\Model\Magento\Product\StockItemFactory $magentoProductStockItemFactory,
        \M2E\Kaufland\Model\Order $order,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Core\Helper\Magento $magentoHelper,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->order = $order;
        $this->transactionFactory = $transactionFactory;
        $this->objectManager = $objectManager;
        $this->globalDataHelper = $globalDataHelper;
        $this->magentoHelper = $magentoHelper;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->magentoProductStockItemFactory = $magentoProductStockItemFactory;
    }

    public function setFlag($action, $flag)
    {
        $this->flags[$action] = (bool)$flag;

        return $this;
    }

    public function getFlag($action)
    {
        if (isset($this->flags[$action])) {
            return $this->flags[$action];
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isNotProcessed()
    {
        return $this->order->getReservationState() == self::STATE_UNKNOWN;
    }

    /**
     * @return bool
     */
    public function isPlaced()
    {
        return $this->order->getReservationState() == self::STATE_PLACED;
    }

    /**
     * @return bool
     */
    public function isReleased()
    {
        return $this->order->getReservationState() == self::STATE_RELEASED;
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return $this->order->getReservationState() == self::STATE_CANCELED;
    }

    /**
     * @return bool
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function place()
    {
        if ($this->isPlaced()) {
            throw new \M2E\Kaufland\Model\Exception\Logic('QTY is already reserved.');
        }

        try {
            $this->order->associateWithStore();
            $this->order->associateItemsWithProducts();

            $this->performAction(self::ACTION_SUB, self::STATE_PLACED);
            if (!$this->isPlaced()) {
                return false;
            }
        } catch (\Exception $e) {
            $message = 'QTY was not reserved. Reason: %msg%';
            if ($e instanceof ProductCreationDisabled) {
                $this->order->addInfoLog($message, ['msg' => $e->getMessage()], [], true);

                return false;
            }

            $this->order->addErrorLog($message, ['msg' => $e->getMessage()]);

            return false;
        }

        $this->addSuccessLogQtyChange();

        return true;
    }

    /**
     * @return bool
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function release()
    {
        if ($this->isReleased()) {
            throw new \M2E\Kaufland\Model\Exception\Logic('QTY is already released.');
        }

        if (!$this->isPlaced()) {
            return false;
        }

        try {
            $this->performAction(self::ACTION_ADD, self::STATE_RELEASED);
            if (!$this->isReleased()) {
                return false;
            }
        } catch (\Exception $e) {
            $this->order->addErrorLog(
                'QTY was not released. Reason: %msg%',
                [
                    'msg' => $e->getMessage(),
                ]
            );

            return false;
        }

        $this->addSuccessLogQtyChange();

        return true;
    }

    /**
     * @return bool
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function cancel()
    {
        if ($this->isCanceled()) {
            throw new \M2E\Kaufland\Model\Exception\Logic('QTY reserve is already canceled.');
        }

        if (!$this->isPlaced()) {
            return false;
        }

        try {
            $this->performAction(self::ACTION_ADD, self::STATE_CANCELED);
            if (!$this->isCanceled()) {
                return false;
            }
        } catch (\Exception $e) {
            $this->order->addErrorLog(
                'QTY reserve was not canceled. Reason: %msg%',
                [
                    'msg' => $e->getMessage(),
                ]
            );

            return false;
        }

        $this->addSuccessLogQtyChange();
        $this->order->addSuccessLog('QTY reserve was canceled.');

        return true;
    }

    /**
     * @param $action
     *
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function getValidatedOrdersItems($action)
    {
        $productsExistCount = 0;
        $validatedOrderItems = [];

        foreach ($this->order->getItemsCollection()->getItems() as $item) {
            /**@var \M2E\Kaufland\Model\Order\Item $item */

            $products = $this->getItemProductsByAction($item, $action);
            if (empty($products)) {
                continue;
            }

            foreach ($products as $key => $productId) {
                $magentoProduct = $this->magentoProductFactory->create();
                $magentoProduct->setStoreId($this->order->getStoreId());
                $magentoProduct->setProductId($productId);

                if (!$magentoProduct->exists()) {
                    $this->order->addWarningLog(
                        'The QTY Reservation action (reserve/release/cancel) has not been performed for
                        Product ID "%id%". It is not exist.',
                        ['!id' => $productId]
                    );
                    continue;
                }

                $productsExistCount++;

                $magentoStockItem = $this->magentoProductStockItemFactory->create([
                    'stockItem' => $magentoProduct->getStockItem(),
                ]);

                if (
                    !$magentoStockItem->canChangeQty() &&
                    $this->order->getLogService()->getInitiator() == \M2E\Core\Helper\Data::INITIATOR_USER
                ) {
                    $this->order->addWarningLog(
                        'The QTY Reservation action (reserve/release/cancel) has not been performed for "%name%"
                        as the "Decrease Stock When Order is Placed" or/and "Manage Stock" options are disabled in
                        your Magento Inventory configurations.',
                        ['!name' => $magentoProduct->getName()]
                    );
                    continue;
                }

                $validatedOrderItems[$item->getId()][$magentoProduct->getProductId()] = [
                    $magentoProduct,
                    $magentoStockItem,
                ];
            }
        }

        if ($productsExistCount === 0) {
            $this->order->setData('reservation_state', self::STATE_UNKNOWN)->save();
            throw new \M2E\Kaufland\Model\Exception\Logic('Product(s) does not exist.');
        }

        return $validatedOrderItems;
    }

    /**
     * @param $action
     * @param $newState
     *
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function performAction($action, $newState)
    {
        $productsAffectedCount = 0;
        $productsChangedCount = 0;
        $validateOrderItems = $this->getValidatedOrdersItems($action);

        $transaction = $this->transactionFactory->create();

        foreach ($this->order->getItemsCollection()->getItems() as $item) {
            /**@var \M2E\Kaufland\Model\Order\Item $item */

            if ($action === self::ACTION_SUB) {
                $qty = $item->getQtyPurchased();
                $item->setData('qty_reserved', $qty);
            } else {
                $qty = $item->getQtyReserved();
                $item->setData('qty_reserved', 0);

                if ($item->getMagentoProduct()->isBundleType()) {
                    $this->ensureParentStockStatusInStock($item->getMagentoProduct()->getProductId());
                }
            }

            $products = isset($validateOrderItems[$item->getId()]) ? $validateOrderItems[$item->getId()] : [];
            if (empty($products)) {
                continue;
            }

            foreach ($products as $productId => $productData) {
                /** @var \M2E\Kaufland\Model\Magento\Product $magentoProduct */
                /** @var \M2E\Kaufland\Model\Magento\Product\StockItem $magentoStockItem */
                [$magentoProduct, $magentoStockItem] = $productData;
                $productsAffectedCount++;

                if ($item->getMagentoProduct()->isBundleType()) {
                    $bundleDefaultQty = $item
                        ->getMagentoProduct()
                        ->getBundleDefaultQty($magentoProduct->getProductId());
                    $qty *= $bundleDefaultQty;
                }

                $changeResult = $this->isMsiMode($magentoProduct)
                    ? $this->changeMSIProductQty($item, $magentoProduct, $magentoStockItem, $action, $qty, $transaction)
                    : $this->changeProductQty($item, $magentoProduct, $magentoStockItem, $action, $qty, $transaction);

                if (!$changeResult) {
                    if ($action === self::ACTION_SUB) {
                        unset($products[$productId]);
                    }

                    continue;
                }

                if ($action === self::ACTION_ADD) {
                    unset($products[$productId]);
                }

                $productsChangedCount++;
                $this->pushQtyChangeInfo($qty, $action, $magentoProduct);
            }

            $item->setReservedProducts(array_keys($products));
            $transaction->addObject($item);
        }

        if ($productsAffectedCount <= 0) {
            return;
        }

        if ($productsChangedCount <= 0 && $action === self::ACTION_SUB) {
            return;
        }

        $this->order->setData('reservation_state', $newState);

        if ($newState === self::STATE_PLACED && !$this->getFlag('order_reservation')) {
            $this->order->setData(
                'reservation_start_date',
                \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'),
            );
        }

        $transaction->addObject($this->order);
        $transaction->save();
    }

    protected function changeProductQty(
        \M2E\Kaufland\Model\Order\Item $item,
        \M2E\Kaufland\Model\Magento\Product $magentoProduct,
        \M2E\Kaufland\Model\Magento\Product\StockItem $magentoStockItem,
        $action,
        $qty,
        \Magento\Framework\DB\Transaction $transaction
    ) {
        if (!$magentoStockItem->canChangeQty()) {
            return false;
        }

        $result = false;

        if ($action === self::ACTION_ADD) {
            $result = $magentoStockItem->addQty($qty, false);
        }

        if ($action === self::ACTION_SUB) {
            $result = $magentoStockItem->subtractQty($qty, false);

            if (
                !$result &&
                !$magentoStockItem->isAllowedQtyBelowZero() &&
                $magentoStockItem->resultOfSubtractingQtyBelowZero($qty)
            ) {
                $this->order->addErrorLog(
                    'QTY wasn’t reserved for "%name%". Magento QTY: "%magento_qty%". Ordered QTY: "%order_qty%".',
                    [
                        '!name' => $magentoProduct->getName(),
                        '!magento_qty' => $magentoStockItem->getStockItem()->getQty(),
                        '!order_qty' => $qty,
                    ]
                );
            }
        }

        if (!$result) {
            return false;
        }

        $transaction->addObject($magentoStockItem->getStockItem());
        $transaction->addCommitCallback([$magentoStockItem, 'afterSave']);

        //--------------------------------------
        if ($magentoProduct->isSimpleType() || $magentoProduct->isDownloadableType()) {
            $item->getProduct()->setStockItem($magentoStockItem->getStockItem());
        }

        /**
         * After making changes to Stock Item, Magento Product model will contain invalid "salable" status.
         * Reset Magento Product model for further reload.
         */
        if ($magentoStockItem->isStockStatusChanged()) {
            $item->setProduct(null);
        }

        //--------------------------------------

        return $result;
    }

    protected function changeMSIProductQty(
        \M2E\Kaufland\Model\Order\Item $item,
        \M2E\Kaufland\Model\Magento\Product $magentoProduct,
        \M2E\Kaufland\Model\Magento\Product\StockItem $magentoStockItem,
        $action,
        $qty,
        \Magento\Framework\DB\Transaction $transaction
    ) {
        $reservationMarkPath = "reservation_msi_used/{$magentoProduct->getProductId()}";

        try {
            if ($action === self::ACTION_ADD) {
                if (!$item->getSetting('product_details', $reservationMarkPath, false)) {
                    return $this->changeProductQty(
                        $item,
                        $magentoProduct,
                        $magentoStockItem,
                        $action,
                        $qty,
                        $transaction
                    );
                }
                $item->setSetting('product_details', $reservationMarkPath, null);
            }

            $stockByWebsiteIdResolver = $this->objectManager->get(StockByWebsiteIdResolverInterface::class);
            $websiteId = (int)$item->getOrder()->getStore()->getWebsiteId();
            $stockId = (int)$stockByWebsiteIdResolver->execute($websiteId)->getStockId();

            if ($action === self::ACTION_SUB) {
                $checkItemsQty = $this->objectManager->get(\Magento\InventorySales\Model\CheckItemsQuantity::class);
                $checkItemsQty->execute([$magentoProduct->getSku() => $qty], $stockId);

                $item->setSetting('product_details', $reservationMarkPath, true);
            }
        } catch (\Exception $e) {
            $message = $action === self::ACTION_SUB
                ? 'QTY for Product "%name%" cannot be reserved. Reason: %msg%'
                : 'QTY reservation for Product "%name%" cannot be released. Reason: %msg%';
            $this->order->addErrorLog(
                $message,
                [
                    '!name' => $magentoProduct->getName(),
                    '!msg' => $e->getMessage(),
                ]
            );

            return false;
        }

        $reservation = $this->objectManager->get(\M2E\Kaufland\Model\MSI\Order\Reserve::class);
        $reservation->placeCompensationReservation(
            [
                [
                    'sku' => $magentoProduct->getSku(),
                    'qty' => $action === self::ACTION_SUB ? -$qty : $qty,
                ],
            ],
            $this->order->getStoreId(),
            [
                'type' => $action === self::ACTION_SUB ? $reservation::EVENT_TYPE_MAGENTO_RESERVATION_PLACED
                    : $reservation::EVENT_TYPE_MAGENTO_RESERVATION_RELEASED,
                'objectType' => $reservation::M2E_ORDER_OBJECT_TYPE,
                'objectId' => (string)$this->order->getId(),
            ]
        );

        $key = 'released_reservation_product_' . $magentoProduct->getSku() . '_' . $stockId;
        if ($action === self::ACTION_ADD && !$this->globalDataHelper->getValue($key)) {
            $this->globalDataHelper->setValue($key, true);
        }

        return true;
    }

    protected function pushQtyChangeInfo($qty, $action, \M2E\Kaufland\Model\Magento\Product $magentoProduct)
    {
        $this->qtyChangeInfo[] = [
            'action' => $action,
            'quantity' => $qty,
            'product_name' => $magentoProduct->getName(),
        ];
    }

    protected function addSuccessLogQtyChange()
    {
        $description = [
            self::ACTION_ADD => 'QTY was released for "%product_name%". Released QTY: %quantity%.',
            self::ACTION_SUB => 'QTY was reserved for "%product_name%". Reserved QTY: %quantity%.',
        ];

        foreach ($this->qtyChangeInfo as $item) {
            $this->order->addSuccessLog(
                $description[$item['action']],
                [
                    '!product_name' => $item['product_name'],
                    '!quantity' => $item['quantity'],
                ]
            );
        }

        $this->qtyChangeInfo = [];
    }

    /**
     * @param Item $item
     * @param $action
     *
     * @return array|mixed|null
     */
    private function getItemProductsByAction(\M2E\Kaufland\Model\Order\Item $item, $action)
    {
        switch ($action) {
            case self::ACTION_ADD:
                return $item->getReservedProducts();

            case self::ACTION_SUB:
                if (
                    $item->getMagentoProductId() &&
                    ($item->getMagentoProduct()->isSimpleType() || $item->getMagentoProduct()->isDownloadableType())
                ) {
                    return [$item->getMagentoProductId()];
                }

                return $item->getAssociatedProducts();
        }
    }

    //########################################

    private function isMsiMode(\M2E\Kaufland\Model\Magento\Product $product)
    {
        if (!$this->magentoHelper->isMSISupportingVersion()) {
            return false;
        }

        if (interface_exists(IsSourceItemManagementAllowedForProductTypeInterface::class)) {
            $isSourceItemManagementAllowedForProductType = $this->objectManager->get(
                IsSourceItemManagementAllowedForProductTypeInterface::class
            );

            return $isSourceItemManagementAllowedForProductType->execute($product->getTypeId());
        }

        return true;
    }

    /**
     * @param int $parentId
     *
     * @return void
     */
    private function ensureParentStockStatusInStock(int $parentId): void
    {
        $scopeId = $this->objectManager
            ->get(\Magento\CatalogInventory\Api\StockConfigurationInterface::class)
            ->getDefaultScopeId();

        $storage = $this->objectManager
            ->get(\Magento\CatalogInventory\Model\StockRegistryStorage::class);

        $stockStatus = $storage->getStockStatus($parentId, $scopeId);
        if ($stockStatus === null) {
            return;
        }

        if ($stockStatus->getStockStatus() === 1) {
            return;
        }

        $stockStatus->setStockStatus(1);
        $storage->setStockStatus($parentId, $scopeId, $stockStatus);
    }
}
