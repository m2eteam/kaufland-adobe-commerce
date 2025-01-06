<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model;

use M2E\Kaufland\Model\ResourceModel\Listing as ListingResource;

class Listing extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public const LOCK_NICK = 'listing';

    public const INSTRUCTION_TYPE_PRODUCT_ADDED = 'listing_product_added';
    public const INSTRUCTION_INITIATOR_ADDING_PRODUCT = 'adding_product_to_listing';

    public const INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER = 'listing_product_moved_from_other';
    public const INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_OTHER = 'moving_product_from_other_to_listing';

    public const INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING = 'listing_product_moved_from_listing';
    public const INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_LISTING = 'moving_product_from_listing_to_listing';

    public const INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING = 'listing_product_remap_from_listing';
    public const INSTRUCTION_INITIATOR_REMAPING_PRODUCT_FROM_LISTING = 'remaping_product_from_listing_to_listing';

    public const CREATE_LISTING_SESSION_DATA = 'kaufland_listing_create';
    public const INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW = 'change_listing_store_view';
    public const INSTRUCTION_INITIATOR_CHANGED_LISTING_STORE_VIEW = 'changed_listing_store_view';

    public const CONDITION_NEW = 'NEW';
    public const CONDITION_USED_GOOD = 'USED___GOOD';
    public const CONDITION_USED_AS_NEW = 'USED___AS_NEW';
    public const CONDITION_USED_VERY_GOOD = 'USED___VERY_GOOD';
    public const CONDITION_USED_ACCEPTABLE = 'USED___ACCEPTABLE';

    private ?\M2E\Kaufland\Model\Account $account = null;
    private ?\M2E\Kaufland\Model\Storefront $storefront = null;

    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;
    private Product\Repository $listingProductRepository;
    private \M2E\Kaufland\Model\Template\SellingFormat\Repository $sellingFormatTemplateRepository;
    private \M2E\Kaufland\Model\Template\Synchronization\Repository $synchronizationTemplateRepository;
    private Product\DeleteService $productDeleteService;
    private \M2E\Kaufland\Model\Listing\LogService $listingLogService;
    /** @var \M2E\Kaufland\Model\Template\Shipping\Repository */
    private Template\Shipping\Repository $shippingTemplateRepository;
    /** @var \M2E\Kaufland\Model\StopQueue\CreateService */
    private StopQueue\CreateService $stopQueueCreateService;
    private \M2E\Kaufland\Model\Listing\Settings\Sku $skuSettings;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        Product\Repository $listingProductRepository,
        \M2E\Kaufland\Model\Template\SellingFormat\Repository $sellingFormatTemplateRepository,
        \M2E\Kaufland\Model\Template\Synchronization\Repository $synchronizationTemplateRepository,
        \M2E\Kaufland\Model\Template\Shipping\Repository $shippingTemplateRepository,
        Product\DeleteService $productDeleteService,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \M2E\Kaufland\Model\StopQueue\CreateService $stopQueueCreateService,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $modelFactory,
            $activeRecordFactory,
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->accountRepository = $accountRepository;
        $this->storefrontRepository = $storefrontRepository;
        $this->listingProductRepository = $listingProductRepository;
        $this->sellingFormatTemplateRepository = $sellingFormatTemplateRepository;
        $this->synchronizationTemplateRepository = $synchronizationTemplateRepository;
        $this->productDeleteService = $productDeleteService;
        $this->listingLogService = $listingLogService;
        $this->shippingTemplateRepository = $shippingTemplateRepository;
        $this->stopQueueCreateService = $stopQueueCreateService;
    }

    // ----------------------------------------

    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\Kaufland\Model\ResourceModel\Listing::class);
    }

    // ----------------------------------------

    /**
     * @return bool
     */
    public function isLocked()
    {
        return count($this->listingProductRepository->findStatusListedByListing($this)) > 0;
    }

    // ----------------------------------------

    public function getAccount(): \M2E\Kaufland\Model\Account
    {
        if (isset($this->account)) {
            return $this->account;
        }

        return $this->account = $this->accountRepository->get($this->getAccountId());
    }

    // ---------------------------------------

    public function getStorefront(): \M2E\Kaufland\Model\Storefront
    {
        if ($this->storefront !== null) {
            return $this->storefront;
        }

        return $this->storefront = $this->storefrontRepository->get($this->getStorefrontId());
    }

    // ----------------------------------------

    /**
     * @return \M2E\Kaufland\Model\Product[]
     */
    public function getProducts(): array
    {
        $products = $this->listingProductRepository->findByListing($this);
        foreach ($products as $product) {
            $product->initListing($this);
        }

        return $products;
    }

    // ----------------------------------------

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getTemplateSellingFormat(): Template\SellingFormat
    {
        return $this->sellingFormatTemplateRepository
            ->get($this->getTemplateSellingFormatId());
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getTemplateSynchronization(): Template\Synchronization
    {
        return $this->synchronizationTemplateRepository
            ->get($this->getTemplateSynchronizationId());
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getTemplateShipping(): Template\Shipping
    {
        return $this->shippingTemplateRepository
            ->get($this->getTemplateShippingId());
    }

    public function getTitle(): string
    {
        return (string)$this->getData(ListingResource::COLUMN_TITLE);
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(ListingResource::COLUMN_ACCOUNT_ID);
    }

    public function getStorefrontId(): int
    {
        return (int)$this->getData(ListingResource::COLUMN_STOREFRONT_ID);
    }

    public function getStoreId(): int
    {
        return (int)$this->getData(ListingResource::COLUMN_STORE_ID);
    }

    public function setStoreId(int $id): void
    {
        $this->setData(ListingResource::COLUMN_STORE_ID, $id);
    }

    public function getCreateDate()
    {
        return $this->getData(ListingResource::COLUMN_CREATE_DATE);
    }

    public function getUpdateDate()
    {
        return $this->getData(ListingResource::COLUMN_UPDATE_DATE);
    }

    public function setTemplateSellingFormatId(int $sellingFormatTemplateId): void
    {
        $this->setData(ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID, $sellingFormatTemplateId);
    }

    public function getTemplateSellingFormatId(): int
    {
        return (int)$this->getData(ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID);
    }

    public function setTemplateSynchronizationId(int $synchronizationTemplateId): void
    {
        $this->setData(ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID, $synchronizationTemplateId);
    }

    public function getTemplateSynchronizationId(): int
    {
        return (int)$this->getData(ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID);
    }

    public function setTemplateShippingId(int $shippingTemplateId): void
    {
        $this->setData(ListingResource::COLUMN_TEMPLATE_SHIPPING_ID, $shippingTemplateId);
    }

    public function getTemplateShippingId(): int
    {
        return (int)$this->getData(ListingResource::COLUMN_TEMPLATE_SHIPPING_ID);
    }

    public function hasDescriptionPolicy(): bool
    {
        return (bool)$this->getTemplateDescriptionId();
    }

    public function setTemplateDescriptionId(int $descriptionTemplateId): void
    {
        $this->setData(ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID, $descriptionTemplateId);
    }

    public function getTemplateDescriptionId(): int
    {
        return (int)$this->getData(ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID);
    }

    public function setConditionValue(string $conditionValue): void
    {
        $this->setData(ListingResource::COLUMN_CONDITION_VALUE, $conditionValue);
    }

    public function getConditionValue(): ?string
    {
        return $this->getData(ListingResource::COLUMN_CONDITION_VALUE);
    }

    // ---------------------------------------

    public function getSkuSettings(): \M2E\Kaufland\Model\Listing\Settings\Sku
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->skuSettings)) {
            return $this->skuSettings;
        }

        $skuSettingsData = [];

        $value = $this->getData(ListingResource::COLUMN_SKU_SETTINGS);
        if (!empty($value)) {
            $skuSettingsData = (array)json_decode($value, true);
        }

        $this->skuSettings = new \M2E\Kaufland\Model\Listing\Settings\Sku();

        if (!empty($skuSettingsData)) {
            $this->skuSettings = $this->skuSettings
                ->createWithSkuMode((int)$skuSettingsData['sku_mode'])
                ->createWithSkuCustomAttribute($skuSettingsData['sku_custom_attribute'])
                ->createWithSkuModificationMode((int)$skuSettingsData['sku_modification_mode'])
                ->createWithSkuModificationCustomValue($skuSettingsData['sku_modification_custom_value'])
                ->createWithGenerateSkuMode((int)$skuSettingsData['generate_sku_mode']);
        }

        return $this->skuSettings;
    }

    public function setSkuSettings(\M2E\Kaufland\Model\Listing\Settings\Sku $skuSettings): self
    {
        $this->skuSettings = $skuSettings;

        $this->setData(ListingResource::COLUMN_SKU_SETTINGS, json_encode([
            'generate_sku_mode' => $this->skuSettings->getGenerateSkuMode(),
            'sku_mode' => $this->skuSettings->getSkuMode(),
            'sku_custom_attribute' => $this->skuSettings->getSkuCustomAttribute(),
            'sku_modification_custom_value' => $this->skuSettings->getSkuModificationCustomValue(),
            'sku_modification_mode' => $this->skuSettings->getSkuModificationMode(),
        ]));

        return $this;
    }

    // ---------------------------------------

    public function isCacheEnabled()
    {
        return true;
    }

    /**
     * @deprecated
     */
    public function updateLastPrimaryCategory($path, $data)
    {
        $settings = $this->getAdditionalData();
        $temp = &$settings;

        $pathCount = count($path);

        foreach ($path as $i => $part) {
            if (!array_key_exists($part, $temp)) {
                $temp[$part] = [];
            }

            if ($i == $pathCount - 1) {
                $temp[$part] = $data;
            }

            $temp = &$temp[$part];
        }

        $this->setAdditionalData($settings);
        $this->save();
    }

    public function getAdditionalData()
    {
        $data = $this->getData(ListingResource::COLUMN_ADDITIONAL_DATA);
        if ($data === null) {
            return [];
        }

        return json_decode($data, true);
    }

    /**
     * @throws \JsonException
     */
    public function setAdditionalData(array $additionalData)
    {
        $this->setData(
            ListingResource::COLUMN_ADDITIONAL_DATA,
            json_encode($additionalData, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @param \Magento\Catalog\Model\Product|int $product
     *
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function removeDeletedProduct($product): void
    {
        $magentoProductId = $product instanceof \Magento\Catalog\Model\Product
            ? (int)$product->getId()
            : (int)$product;

        $listingsProducts = $this->listingProductRepository
            ->findByMagentoProductId($magentoProductId);

        $processedListings = [];
        foreach ($listingsProducts as $listingProduct) {
            $message = (string)__('Item was deleted from Magento.');
            if ($listingProduct->getStatus() !== \M2E\Kaufland\Model\Product::STATUS_NOT_LISTED) {
                $message = (string)__('Item was deleted from Magento and stopped on the Channel.');
            }

            if ($listingProduct->isStoppable()) {
                $this->stopQueueCreateService->create($listingProduct);
            }

            $listingProduct->setStatusInactive();
            $this->listingProductRepository->save($listingProduct);

            $this->productDeleteService->process($listingProduct);

            $listingId = $listingProduct->getListingId();
            if (isset($processedListings[$listingId])) {
                continue;
            }

            $processedListings[$listingId] = true;

            $this->listingLogService->addProduct(
                $listingProduct,
                \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
                \M2E\Kaufland\Model\Listing\Log::ACTION_DELETE_PRODUCT_FROM_MAGENTO,
                null,
                $message,
                \M2E\Kaufland\Model\Log\AbstractModel::TYPE_WARNING
            );
        }
    }

    public function deleteListingProductsForce(): void
    {
        $listingProducts = $this->getProducts();

        foreach ($listingProducts as $listingProduct) {
            $listingProduct->canBeForceDeleted(true);
            $this->productDeleteService->process($listingProduct);
        }
    }

    public function delete()
    {
        if ($this->isLocked()) {
            return false;
        }

        $products = $this->getProducts();
        foreach ($products as $product) {
            $this->productDeleteService->process($product);
        }

        $this->listingLogService->addListing(
            $this,
            \M2E\Core\Helper\Data::INITIATOR_UNKNOWN,
            \M2E\Kaufland\Model\Listing\Log::ACTION_DELETE_LISTING,
            null,
            (string)__('Listing was deleted'),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO
        );

        unset($this->account);

        return parent::delete();
    }
}
