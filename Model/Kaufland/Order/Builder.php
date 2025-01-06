<?php

namespace M2E\Kaufland\Model\Kaufland\Order;

class Builder extends \Magento\Framework\DataObject
{
    public const STATUS_NOT_MODIFIED = 0;
    public const STATUS_NEW = 1;
    public const STATUS_UPDATED = 2;

    private \M2E\Kaufland\Model\Storefront $storefront;
    private \M2E\Kaufland\Model\Order $order;
    private int $status = self::STATUS_NOT_MODIFIED;
    private array $items = [];
    private array $deliveryInfo = [];
    private bool $isOrderStatusHasUpdated = false;

    // ----------------------------------------

    private \M2E\Kaufland\Model\Magento\Order\Updater $magentoOrderUpdater;
    private \M2E\Kaufland\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory;
    private \M2E\Kaufland\Model\Kaufland\Order\StatusResolver $orderStatusResolver;
    private \M2E\Kaufland\Model\OrderFactory $orderFactory;
    private \M2E\Kaufland\Model\Kaufland\Order\Item\BuilderFactory $orderItemBuilderFactory;
    private \M2E\Kaufland\Model\Account $account;
    private \M2E\Kaufland\Model\Kaufland\Order\ShippingResolver $shippingResolver;
    /** @var \M2E\Kaufland\Model\Kaufland\Order\TaxResolver */
    private TaxResolver $taxResolver;
    private \M2E\Kaufland\Block\Adminhtml\Kaufland\Order\StatusHelper $orderStatusHelper;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Kaufland\Order\StatusHelper $orderStatusHelper,
        \M2E\Kaufland\Model\Kaufland\Order\Item\BuilderFactory $orderItemBuilderFactory,
        \M2E\Kaufland\Model\Magento\Order\Updater $magentoOrderUpdater,
        \M2E\Kaufland\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \M2E\Kaufland\Model\Kaufland\Order\StatusResolver $orderStatusResolver,
        \M2E\Kaufland\Model\Kaufland\Order\ShippingResolver $shippingResolver,
        \M2E\Kaufland\Model\Kaufland\Order\TaxResolver $taxResolver,
        \M2E\Kaufland\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->orderStatusResolver = $orderStatusResolver;
        $this->magentoOrderUpdater = $magentoOrderUpdater;
        $this->orderItemBuilderFactory = $orderItemBuilderFactory;
        $this->shippingResolver = $shippingResolver;
        $this->taxResolver = $taxResolver;
        $this->orderStatusHelper = $orderStatusHelper;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function initialize(
        \M2E\Kaufland\Model\Account $account,
        array $data
    ): void {
        $this->account = $account;
        $this->storefront = $this->account->getStorefrontByCode($data['storefront_code']);

        $this->initializeData($data);
        $this->initializeOrder();
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function initializeData(array $data = []): void
    {
        $this->setData('account_id', $this->account->getId());
        $this->setData('storefront_id', $this->storefront->getId());

        $this->setData('kaufland_order_id', $data['order_id']);
        $this->setData(
            'order_status',
            $this->orderStatusResolver->getOrderStatusResolver($data['items'])
        );

        $this->setData('purchase_update_date', $data['update_date']);
        $this->setData('purchase_create_date', $data['create_date']);

        $this->setData('currency', $this->storefront->getCurrencyCode());

        // Tax

        $firstOrderItem = reset($data['items']);
        $vatRate = $this->taxResolver->getVatbyStorefrontCode($firstOrderItem['storefront_code']);
        $totalTaxAmount = $this->taxResolver->getOrderTax($data['items'], (float)$vatRate);

        $taxDetails = [
            'amount' => $totalTaxAmount,
            'rate' => $vatRate,
            'is_vat' => true,
        ];

        $this->setData('tax_details', \M2E\Core\Helper\Json::encode($taxDetails));

        // Buyer
        $this->setData('buyer_user_id', trim($data['buyer']['id']));
        $this->setData('buyer_email', trim($data['buyer']['email']));

        // Shipping
        $shippingDetails = [];

        $shippingDetails['price'] = $this->shippingResolver->getShippingRate($data['items']);
        $shippingDetails['service'] = $data['delivery']['services'];

        $shippingAddress = [];
        $name = $data['shipping']['first_name'] . ' ' . $data['shipping']['last_name'];
        $shippingAddress['buyer_name'] = $name;
        $shippingAddress['buyer_email'] = trim($data['buyer']['email']);
        $shippingAddress['recipient_name'] = $name;

        $shippingAddress['street'] = $data['shipping']['street'];
        $shippingAddress['house_number'] = $data['shipping']['house_number'];
        $shippingAddress['company_name'] = $data['shipping']['company_name'];

        $shippingAddress['city'] = $data['shipping']['city'];
        $shippingAddress['country_code'] = $data['shipping']['country'];
        $shippingAddress['postal_code'] = $data['shipping']['post_code'];
        $shippingAddress['phone'] = $data['shipping']['phone'];
        $shippingDetails['address'] = $shippingAddress;

        $this->setData(\M2E\Kaufland\Model\ResourceModel\Order::COLUMN_SHIPPING_DETAILS, \M2E\Core\Helper\Json::encode($shippingDetails));

        // Billing
        $billingDetails = [];
        $billingDetails['first_name'] = $data['billing']['first_name'];
        $billingDetails['last_name'] = $data['billing']['last_name'];
        $billingDetails['postal_code'] = $data['billing']['post_code'];
        $billingDetails['country_code'] = $data['billing']['country'];
        $billingDetails['city'] = $data['billing']['city'];
        $billingDetails['street'] = $data['billing']['street'];
        $billingDetails['phone'] = $data['billing']['phone'];
        $billingDetails['company_name'] = $data['billing']['company_name'];

        $this->setData(\M2E\Kaufland\Model\ResourceModel\Order::COLUMN_BILLING_DETAILS, \M2E\Core\Helper\Json::encode($billingDetails));

        $this->setData('delivery_time_expires_date', $this->shippingResolver->getShippingDate($data['items']));

        // ---------------------------------------

        // trackingDetail
        $trackingDetails = [
            'shipping_provider' => $data['delivery']['provider'],
            'shipping_carrier' => $data['delivery']['carrier'],
            'shipping_services' => $data['delivery']['services'],
            'pickup_location_id' => $data['delivery']['pickup_location_id'],
            'tracking_number' => $data['delivery']['dhl_post_number'] ?? '',
        ];

        $this->setData('tracking_details', \M2E\Core\Helper\Json::encode($trackingDetails));

        $this->deliveryInfo = $data['delivery'];
        $this->items = $data['items'];
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function initializeOrder()
    {
        $this->status = self::STATUS_NOT_MODIFIED;

        $existOrder = $this->getExistedOrders();

        // New order
        // ---------------------------------------
        if ($existOrder === null) {
            $this->status = self::STATUS_NEW;
            $this->order = $this->orderFactory->create();
            $this->order->markStatusUpdateRequired();

            return;
        }

        // ---------------------------------------

        // Already exist order
        // ---------------------------------------
        $this->order = $existOrder;
        $this->status = self::STATUS_UPDATED;

        $this->order->markStatusUpdateRequired();
        // ---------------------------------------
    }

    /**
     * @return \M2E\Kaufland\Model\Order
     */
    protected function getExistedOrders(): ?\M2E\Kaufland\Model\Order
    {
        $orderId = $this->getData('kaufland_order_id');
        $storefrontId = $this->getData('storefront_id');

        $collection = $this->orderCollectionFactory->create();

        $collection->addFieldToFilter('account_id', ['eq' => $this->account->getId()]);
        $collection->addFieldToFilter('kaufland_order_id', ['eq' => $orderId]);
        $collection->addFieldToFilter('storefront_id', ['eq' => $storefrontId]);
        $collection->setOrder('id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);

        return $collection->getFirstItem();
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function process(): ?\M2E\Kaufland\Model\Order
    {
        if (!$this->canCreateOrUpdateOrder()) {
            return null;
        }

        $this->checkUpdates();

        $this->createOrUpdateOrder();
        $this->createOrUpdateItems();

        if ($this->isNew()) {
            $this->processNew();
        }

        if ($this->isUpdated()) {
            $this->processOrderUpdates();
            $this->processMagentoOrderUpdates();
        }

        return $this->order;
    }

    //########################################

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Exception
     */
    protected function createOrUpdateItems()
    {
        $itemsCollection = $this->order->getItemsCollection();
        $itemsCollection->load();

        foreach ($this->items as $orderItemData) {
            $orderItemData['order_id'] = $this->order->getId();
            $itemBuilder = $this->orderItemBuilderFactory->create();
            $itemBuilder->initialize($orderItemData);

            $item = $itemBuilder->process();
            $item->setOrder($this->order);

            $itemsCollection->removeItemByKey($item->getId());
            $itemsCollection->addItem($item);
        }
    }

    // ---------------------------------------

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isUpdated(): bool
    {
        return $this->status === self::STATUS_UPDATED;
    }

    //########################################

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Exception
     */
    protected function canCreateOrUpdateOrder(): bool
    {
        if ($this->order->getId()) {
            $newPurchaseUpdateDate = \M2E\Core\Helper\Date::createDateGmt(
                $this->getData('purchase_update_date')
            );
            $oldPurchaseUpdateDate = \M2E\Core\Helper\Date::createDateGmt(
                $this->order->getPurchaseUpdateDate()
            );

            if ($oldPurchaseUpdateDate > $newPurchaseUpdateDate) {
                return false;
            }
        }

        return true;
    }

    protected function createOrUpdateOrder(): void
    {
        foreach ($this->getData() as $key => $value) {
            if (
                !$this->order->getId()
                || ($this->order->hasData($key) && $this->order->getData($key) != $value)
            ) {
                $this->order->addData($this->getData());
                $this->order->save();
                break;
            }
        }

        $this->order->setAccount($this->account);

        if ($this->order->isCanceled() && $this->order->getReserve()->isPlaced()) {
            $this->order->getReserve()->cancel();
        }
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function processNew(): void
    {
        if (!$this->isNew()) {
            return;
        }

        $kauflandAccount = $this->account;

        if (
            $this->order->hasListingProductItems()
            && !$kauflandAccount->isMagentoOrdersListingsModeEnabled()
        ) {
            return;
        }

        if (
            $this->order->hasOtherListingItems()
            && !$kauflandAccount->isMagentoOrdersListingsOtherModeEnabled()
        ) {
            return;
        }

        if (!$this->order->canCreateMagentoOrder()) {
            $this->order->addWarningLog(
                'Magento Order was not created. Reason: %msg%',
                [
                    'msg' => 'Order Creation Rules were not met. ' .
                        'Press Create Order Button at Order View Page to create it anyway.',
                ]
            );
        }
    }

    protected function checkUpdates(): void
    {
        if (!$this->isUpdated()) {
            return;
        }

        if ($this->getData('order_status') !== $this->order->getOrderStatus()) {
            $this->isOrderStatusHasUpdated = true;
        }
    }

    /**
     * @return bool
     */
    protected function hasUpdates(): bool
    {
        return $this->isOrderStatusHasUpdated;
    }

    protected function processOrderUpdates()
    {
        if (!$this->hasUpdates()) {
            return;
        }

        $this->order->addSuccessLog(
            sprintf(
                'Order status was updated to %s on Kaufland',
                $this->orderStatusHelper->getStatusLabel($this->order->getOrderStatus())
            )
        );
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function processMagentoOrderUpdates()
    {
        if (!$this->hasUpdates()) {
            return;
        }

        $magentoOrder = $this->order->getMagentoOrder();
        if ($magentoOrder === null) {
            return;
        }

        $magentoOrderUpdater = $this->magentoOrderUpdater;
        $magentoOrderUpdater->setMagentoOrder($magentoOrder);
        $magentoOrderUpdater->updateStatus($this->order->getStatusForMagentoOrder());

        $proxy = $this->order->getProxy();
        $proxy->setStore($this->order->getStore());

        $magentoOrderUpdater->finishUpdate();
    }
}
