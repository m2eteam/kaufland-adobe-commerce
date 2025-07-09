<?php

namespace M2E\Kaufland\Model;

use M2E\Kaufland\Model\ResourceModel\Account as AccountResource;

class Account extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public const MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO = 'magento';
    public const MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL = 'channel';

    public const MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT = 0;
    public const MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM = 1;

    public const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE = 0;
    public const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT = 1;

    public const MAGENTO_ORDERS_CUSTOMER_MODE_GUEST = 0;
    public const MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED = 1;
    public const MAGENTO_ORDERS_CUSTOMER_MODE_NEW = 2;

    public const USE_SHIPPING_ADDRESS_AS_BILLING_ALWAYS = 0;
    public const USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT = 1;

    public const MAGENTO_ORDERS_CREATE_CHECKOUT = 2;
    public const MAGENTO_ORDERS_CREATE_CHECKOUT_AND_PAID = 4;

    public const MAGENTO_ORDERS_TAX_MODE_NONE = 0;
    public const MAGENTO_ORDERS_TAX_MODE_CHANNEL = 1;
    public const MAGENTO_ORDERS_TAX_MODE_MAGENTO = 2;
    public const MAGENTO_ORDERS_TAX_MODE_MIXED = 3;

    public const MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT = 0;
    public const MAGENTO_ORDERS_STATUS_MAPPING_MODE_CUSTOM = 1;

    public const MAGENTO_ORDERS_STATUS_MAPPING_NEW = 'pending';
    public const MAGENTO_ORDERS_STATUS_MAPPING_PAID = 'processing';
    public const MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED = 'complete';

    public const OTHER_LISTINGS_MAPPING_EAN_MODE_NONE = 0;
    public const OTHER_LISTINGS_MAPPING_EAN_MODE_DEFAULT = 1;
    public const OTHER_LISTINGS_MAPPING_EAN_MODE_CUSTOM_ATTRIBUTE = 2;

    public const OTHER_LISTINGS_MAPPING_SKU_MODE_NONE = 0;
    public const OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT = 1;
    public const OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID = 2;
    public const OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE = 3;

    public const OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_NONE = 0;
    public const OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_CUSTOM_ATTRIBUTE = 1;

    public const OTHER_LISTINGS_MAPPING_SKU_DEFAULT_PRIORITY = 1;
    public const OTHER_LISTINGS_MAPPING_EAN_DEFAULT_PRIORITY = 2;
    public const OTHER_LISTINGS_MAPPING_ITEM_ID_DEFAULT_PRIORITY = 3;

    private \M2E\Kaufland\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private Storefront\Repository $storefrontRepository;

    /** @var \M2E\Kaufland\Model\Storefront[] */
    private array $storefronts;
    private Warehouse\Repository $warehouseRepository;

    /** @var \M2E\Kaufland\Model\Warehouse[] */
    private array $warehouses;
    private ShippingGroup\Repository $shippingGroupRepository;

    /** @var \M2E\Kaufland\Model\ShippingGroup[] */
    private array $shippingGroups;
    private Account\Settings\UnmanagedListings $unmanagedListingSettings;
    private Account\Settings\Order $ordersSettings;
    private Account\Settings\InvoicesAndShipment $invoiceAndShipmentSettings;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        Storefront\Repository $storefrontRepository,
        Warehouse\Repository $warehouseRepository,
        ShippingGroup\Repository $shippingGroupsRepository,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $modelFactory,
            $activeRecordFactory,
            $resource,
            $resourceCollection,
        );
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->storefrontRepository = $storefrontRepository;
        $this->warehouseRepository = $warehouseRepository;
        $this->shippingGroupRepository = $shippingGroupsRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\Kaufland\Model\ResourceModel\Account::class);
    }

    public function init(
        string $title,
        string $serverHash,
        string $identifier,
        \M2E\Kaufland\Model\Account\Settings\UnmanagedListings $unmanagedListingsSettings,
        \M2E\Kaufland\Model\Account\Settings\Order $orderSettings,
        \M2E\Kaufland\Model\Account\Settings\InvoicesAndShipment $invoicesAndShipmentSettings
    ): self {
        $this
            ->setTitle($title)
            ->setData(AccountResource::COLUMN_SERVER_HASH, $serverHash)
            ->setData(AccountResource::COLUMN_IDENTIFIER, $identifier)
            ->setUnmanagedListingSettings($unmanagedListingsSettings)
            ->setOrdersSettings($orderSettings)
            ->setInvoiceAndShipmentSettings($invoicesAndShipmentSettings);

        $this->storefronts = [];
        $this->warehouses = [];

        return $this;
    }

    public function hasIdentifier(): bool
    {
        return $this->getData(AccountResource::COLUMN_IDENTIFIER) !== null;
    }

    // ----------------------------------------

    /**
     * @param Storefront[] $storefronts
     *
     * @return $this
     */
    public function setStorefronts(array $storefronts): self
    {
        $this->storefronts = $storefronts;
        foreach ($this->storefronts as $storefronts) {
            $storefronts->loadAccount($this);
        }

        return $this;
    }

    /**
     * @return \M2E\Kaufland\Model\Storefront[]
     */
    public function getStorefronts(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->storefronts)) {
            return $this->storefronts;
        }

        $this->storefronts = $this->storefrontRepository->findForAccount($this->getId());
        foreach ($this->storefronts as $storefront) {
            $storefront->loadAccount($this);
        }

        return $this->storefronts;
    }

    public function getStorefrontByCode(string $code): Storefront
    {
        foreach ($this->getStorefronts() as $storefront) {
            if ($storefront->getStorefrontCode() === $code) {
                return $storefront;
            }
        }

        throw new \M2E\Kaufland\Model\Exception\Logic((string)__('Storefront %code not found.', ['code' => $code]));
    }

    public function findStorefrontByCode(string $code): ?Storefront
    {
        foreach ($this->getStorefronts() as $storefront) {
            if ($storefront->getStorefrontCode() === $code) {
                return $storefront;
            }
        }

        return null;
    }

    /**
     * @return \M2E\Kaufland\Model\Warehouse[]
     */
    public function getWarehouses(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->warehouses)) {
            return $this->warehouses;
        }

        $this->warehouses = $this->warehouseRepository->findByAccount($this->getId());
        foreach ($this->warehouses as $warehouse) {
            $warehouse->setAccount($this);
        }

        return $this->warehouses;
    }

    /**
     * @param \M2E\Kaufland\Model\Warehouse[] $storefronts
     *
     * @return $this
     */
    public function setWarehouses(array $warehouses): self
    {
        $this->warehouses = $warehouses;
        foreach ($this->warehouses as $warehouse) {
            $warehouse->setAccount($this);
        }

        return $this;
    }

    /**
     * @return \M2E\Kaufland\Model\ShippingGroup[]
     */
    public function getShippingGroups(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->shippingGroups)) {
            return $this->shippingGroups;
        }

        $this->shippingGroups = $this->shippingGroupRepository->findByAccount($this->getId());
        foreach ($this->shippingGroups as $shippingGroup) {
            $shippingGroup->setAccount($this);
        }

        return $this->shippingGroups;
    }

    /**
     * @param \M2E\Kaufland\Model\ShippingGroup[] $shippingGroups
     *
     * @return $this
     */
    public function setShippingGroups(array $shippingGroups): self
    {
        $this->shippingGroups = $shippingGroups;
        foreach ($this->shippingGroups as $shippingGroup) {
            $shippingGroup->setAccount($this);
        }

        return $this;
    }

    /**
     * @return \M2E\Kaufland\Model\Listing[]
     */
    public function getListings(): array
    {
        $listingCollection = $this->listingCollectionFactory->create();
        $listingCollection->addFieldToFilter('account_id', $this->getId());

        return $listingCollection->getItems();
    }

    // ----------------------------------------

    public function setTitle(string $title): self
    {
        $this->setData(AccountResource::COLUMN_TITLE, $title);

        return $this;
    }

    public function getTitle()
    {
        return $this->getData(AccountResource::COLUMN_TITLE);
    }

    public function getServerHash()
    {
        return $this->getData(AccountResource::COLUMN_SERVER_HASH);
    }

    // ----------------------------------------

    public function isMagentoOrdersListingsModeEnabled(): bool
    {
        $setting = $this->getSetting(
            AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS,
            ['listing', 'mode'],
            1
        );

        return $setting == 1;
    }

    public function isMagentoOrdersListingsStoreCustom(): bool
    {
        $setting = $this->getSetting(
            AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS,
            ['listing', 'store_mode'],
            self::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT
        );

        return $setting == self::MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM;
    }

    public function getMagentoOrdersListingsStoreId(): int
    {
        $setting = $this->getSetting(
            AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS,
            ['listing', 'store_id'],
            0
        );

        return (int)$setting;
    }

    public function isMagentoOrdersListingsOtherModeEnabled(): bool
    {
        $isCreateOrdersInMagento = (int)$this->getSetting(
            AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS,
            ['listing_other', 'mode'],
            1
        );

        return $isCreateOrdersInMagento === 1;
    }

    public function getMagentoOrdersListingsOtherStoreId(): int
    {
        $setting = $this->getSetting(
            AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS,
            ['listing_other', 'store_id'],
            0
        );

        return (int)$setting;
    }

    public function isMagentoOrdersListingsOtherProductImportEnabled(): bool
    {
        $setting = $this->getSetting(
            AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS,
            ['listing_other', 'product_mode'],
            self::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT
        );

        return $setting == self::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT;
    }

    public function getMagentoOrdersListingsOtherProductTaxClassId(): int
    {
        $setting = $this->getSetting(
            AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS,
            ['listing_other', 'product_tax_class_id'],
            \M2E\Kaufland\Model\Magento\Product::TAX_CLASS_ID_NONE
        );

        return (int)$setting;
    }

    // ----------------------------------------

    public function setUnmanagedListingSettings(
        \M2E\Kaufland\Model\Account\Settings\UnmanagedListings $settings
    ): self {
        $this->unmanagedListingSettings = $settings;
        $this
            ->setData(AccountResource::COLUMN_OTHER_LISTINGS_SYNCHRONIZATION, (int)$settings->isSyncEnabled())
            ->setData(AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_MODE, (int)$settings->isMappingEnabled())
            ->setData(
                AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_SETTINGS,
                json_encode(
                    [
                        'sku' => $settings->getMappingBySkuSettings(),
                        'ean' => $settings->getMappingByEanSettings(),
                        'item_id' => $settings->getMappingByItemIdSettings(),
                    ],
                ),
            )
            ->setData(
                AccountResource::COLUMN_OTHER_LISTINGS_RELATED_STORE_ID,
                $settings->getRelatedStoreId(),
            );

        return $this;
    }

    public function getUnmanagedListingSettings(): \M2E\Kaufland\Model\Account\Settings\UnmanagedListings
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->unmanagedListingSettings)) {
            return $this->unmanagedListingSettings;
        }

        $mappingSettings = $this->getData(AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_SETTINGS);
        $mappingSettings = json_decode($mappingSettings, true);

        $settings = new \M2E\Kaufland\Model\Account\Settings\UnmanagedListings();

        return $this->unmanagedListingSettings = $settings
            ->createWithSync((bool)$this->getData(AccountResource::COLUMN_OTHER_LISTINGS_SYNCHRONIZATION))
            ->createWithMapping((bool)$this->getData(AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_MODE))
            ->createWithMappingSettings(
                $mappingSettings['sku'] ?? [],
                $mappingSettings['ean'] ?? [],
                $mappingSettings['item_id'] ?? [],
            )
            ->createWithRelatedStoreId(
                (int)$this->getData(AccountResource::COLUMN_OTHER_LISTINGS_RELATED_STORE_ID),
            );
    }

    public function setOrdersSettings(\M2E\Kaufland\Model\Account\Settings\Order $settings): self
    {
        $this->ordersSettings = $settings;

        $data = $settings->toArray();

        $this->setData(AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS, json_encode($data));

        return $this;
    }

    public function getOrdersSettings(): \M2E\Kaufland\Model\Account\Settings\Order
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->ordersSettings)) {
            return $this->ordersSettings;
        }

        $data = json_decode($this->getData(AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS), true);

        $settings = new \M2E\Kaufland\Model\Account\Settings\Order();

        return $this->ordersSettings = $settings->createWith($data);
    }

    public function setInvoiceAndShipmentSettings(
        \M2E\Kaufland\Model\Account\Settings\InvoicesAndShipment $settings
    ): self {
        $this->invoiceAndShipmentSettings = $settings;

        $this
            ->setData(AccountResource::COLUMN_CREATE_MAGENTO_INVOICE, (int)$settings->isCreateMagentoInvoice())
            ->setData(AccountResource::COLUMN_UPLOAD_MAGENTO_INVOICE, (int)$settings->isUploadMagentoInvoice())
            ->setData(AccountResource::COLUMN_CREATE_MAGENTO_SHIPMENT, (int)$settings->isCreateMagentoShipment());

        return $this;
    }

    public function getInvoiceAndShipmentSettings(): \M2E\Kaufland\Model\Account\Settings\InvoicesAndShipment
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->invoiceAndShipmentSettings)) {
            return $this->invoiceAndShipmentSettings;
        }

        $settings = new \M2E\Kaufland\Model\Account\Settings\InvoicesAndShipment();

        return $this->invoiceAndShipmentSettings = $settings
            ->createWithMagentoInvoice((bool)$this->getData(AccountResource::COLUMN_CREATE_MAGENTO_INVOICE))
            ->uploadMagentoInvoice((bool)$this->getData(AccountResource::COLUMN_UPLOAD_MAGENTO_INVOICE))
            ->createWithMagentoShipment((bool)$this->getData(AccountResource::COLUMN_CREATE_MAGENTO_SHIPMENT));
    }

    // ----------------------------------------

    public function setOrdersLastSyncDate(\DateTime $date): self
    {
        $this->setData(AccountResource::COLUMN_ORDER_LAST_SYNC, $date);

        return $this;
    }

    public function getOrdersLastSyncDate(): ?\DateTime
    {
        $value = $this->getData(AccountResource::COLUMN_ORDER_LAST_SYNC);
        if (empty($value)) {
            return null;
        }

        return \M2E\Core\Helper\Date::createDateGmt($value);
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt($this->getData(AccountResource::COLUMN_CREATE_DATE));
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersStatusMappingDefault(): bool
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['order_status_mapping', 'mode'],
            \M2E\Kaufland\Model\Account\Settings\Order::ORDERS_STATUS_MAPPING_MODE_DEFAULT
        );

        return $setting == \M2E\Kaufland\Model\Account\Settings\Order::ORDERS_STATUS_MAPPING_MODE_DEFAULT;
    }

    public function getMagentoOrdersStatusProcessing(): string
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return \M2E\Kaufland\Model\Account\Settings\Order::ORDERS_STATUS_MAPPING_PROCESSING;
        }

        return $this->getSetting('magento_orders_settings', ['order_status_mapping', 'processing']);
    }

    public function getMagentoOrdersStatusShipped(): string
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return \M2E\Kaufland\Model\Account\Settings\Order::ORDERS_STATUS_MAPPING_SHIPPED;
        }

        return $this->getSetting('magento_orders_settings', ['order_status_mapping', 'shipped']);
    }
}
