<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model;

use M2E\Kaufland\Model\ResourceModel\Product as ProductResource;

/**
 * @method \M2E\Kaufland\Model\ResourceModel\Product getResource()
 * @method Product\Action\Configurator getActionConfigurator()
 * @method setActionConfigurator(Product\Action\Configurator $configurator)
 */
class Product extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public const ACTION_LIST_UNIT = 1;
    public const ACTION_LIST_PRODUCT = 8;
    public const ACTION_RELIST_UNIT = 2;
    public const ACTION_REVISE_UNIT = 3;
    public const ACTION_REVISE_PRODUCT = 9;
    public const ACTION_STOP_UNIT = 4;
    public const ACTION_DELETE_UNIT = 5;

    public const STATUS_NOT_LISTED = 0;
    public const STATUS_LISTED = 2;
    public const STATUS_INACTIVE = 8;

    public const STATUS_CHANGER_UNKNOWN = 0;
    public const STATUS_CHANGER_SYNCH = 1;
    public const STATUS_CHANGER_USER = 2;
    public const STATUS_CHANGER_COMPONENT = 3;
    public const STATUS_CHANGER_OBSERVER = 4;

    public const MOVING_LISTING_OTHER_SOURCE_KEY = 'moved_from_listing_other_id';

    public const GROUPED_PRODUCT_MODE_OPTIONS = 0;
    public const GROUPED_PRODUCT_MODE_SET = 1;

    public const INSTRUCTION_TYPE_PRODUCT_ACTIVE = 'product_active';
    public const INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED = 'channel_status_changed';
    public const INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED = 'channel_qty_changed';
    public const INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED = 'channel_price_changed';

    public const SEARCH_STATUS_NONE = 0;
    public const SEARCH_STATUS_COMPLETED = 1;

    /**
     * It allows to delete an object without checking if it is isLocked()
     * @var bool
     */
    protected bool $canBeForceDeleted = false;

    private \M2E\Kaufland\Model\Listing $listing;

    protected ?\M2E\Kaufland\Model\Magento\Product\Cache $magentoProductModel = null;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;
    private ?Category\Dictionary $categoryDictionary = null;
    private Product\PriceCalculatorFactory $priceCalculatorFactory;
    private \M2E\Kaufland\Model\Magento\Product\CacheFactory $magentoProductFactory;
    private \M2E\Kaufland\Model\Product\QtyCalculatorFactory $qtyCalculatorFactory;
    private \M2E\Kaufland\Model\Product\Description\RendererFactory $descriptionRendererFactory;
    private \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionaryRepository;
    private \M2E\Kaufland\Model\Policy\ShippingDataProviderFactory $shippingDataProviderFactory;
    private \M2E\Kaufland\Model\Product\SkuGeneratorFactory $skuGeneratorFactory;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Model\Magento\Product\CacheFactory $magentoProductFactory,
        Product\PriceCalculatorFactory $priceCalculatorFactory,
        Product\QtyCalculatorFactory $qtyCalculatorFactory,
        \M2E\Kaufland\Model\Policy\ShippingDataProviderFactory $shippingDataProviderFactory,
        \M2E\Kaufland\Model\Product\SkuGeneratorFactory $skuGeneratorFactory,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        Product\Description\RendererFactory $descriptionRendererFactory,
        \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionaryRepository,
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
        $this->listingRepository = $listingRepository;
        $this->priceCalculatorFactory = $priceCalculatorFactory;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->qtyCalculatorFactory = $qtyCalculatorFactory;
        $this->descriptionRendererFactory = $descriptionRendererFactory;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->shippingDataProviderFactory = $shippingDataProviderFactory;
        $this->skuGeneratorFactory = $skuGeneratorFactory;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\Kaufland\Model\ResourceModel\Product::class);
    }

    public function init(int $listingId, int $magentoProductId, ?string $kauflandProductId, bool $isCreator): self
    {
        $this->setListingId($listingId)
             ->setMagentoProductId($magentoProductId)
             ->setStatusNotListed(self::STATUS_CHANGER_USER)
             ->setKauflandProductCreator($isCreator);

        if ($kauflandProductId !== null) {
            $this->setKauflandProductId($kauflandProductId);
        }

        return $this;
    }

    public function setStatusNotListed(int $changer): self
    {
        $this->setStatus(self::STATUS_NOT_LISTED, $changer)
             ->setData(ProductResource::COLUMN_OFFER_ID, null)
             ->setData(ProductResource::COLUMN_ONLINE_TITLE, null)
             ->setData(ProductResource::COLUMN_ONLINE_DESCRIPTION, null)
             ->setData(ProductResource::COLUMN_ONLINE_IMAGE, null)
             ->setData(ProductResource::COLUMN_ONLINE_CATEGORIES_DATA, null)
             ->setData(ProductResource::COLUMN_ONLINE_QTY, null)
             ->setData(ProductResource::COLUMN_ONLINE_CATEGORY_ID, null)
             ->setData(ProductResource::COLUMN_ONLINE_CATEGORIES_ATTRIBUTES_DATA, null);

        if ($this->isIncomplete()) {
            $this->makeComplete();
        }

        return $this;
    }

    public function fillFromUnmanagedProduct(\M2E\Kaufland\Model\Listing\Other $unmanagedProduct): self
    {
        $this->setKauflandProductId($unmanagedProduct->getKauflandProductId())
             ->setStatus($unmanagedProduct->getStatus(), self::STATUS_CHANGER_COMPONENT)
             ->setUnitId($unmanagedProduct->getUnitId())
             ->setKauflandOfferId($unmanagedProduct->getOfferId())
             ->setStoreFrontId($unmanagedProduct->getStorefrontId())
             ->setOnlineQty($unmanagedProduct->getQty())
             ->setOnlinePrice($unmanagedProduct->getPrice());

        if ($unmanagedProduct->getCategoryId() !== 0) {
            $this->setOnlineCategoryId($unmanagedProduct->getCategoryId());
            $this->setOnlineCategoryData($unmanagedProduct->getCategoryTitle());
        }

        $additionalData = $this->getAdditionalData();
        $additionalData[self::MOVING_LISTING_OTHER_SOURCE_KEY] = $unmanagedProduct->getId();

        $this->setAdditionalData($additionalData);

        return $this;
    }

    public function initListing(\M2E\Kaufland\Model\Listing $listing): void
    {
        $this->listing = $listing;
    }

    public function getListing(): \M2E\Kaufland\Model\Listing
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->listing)) {
            $this->listing = $this->listingRepository->get($this->getListingId());
        }

        return $this->listing;
    }

    // ---------------------------------------

    /**
     * @return \M2E\Kaufland\Model\Magento\Product\Cache
     */
    public function getMagentoProduct(): \M2E\Kaufland\Model\Magento\Product\Cache
    {
        if ($this->magentoProductModel === null) {
            $this->magentoProductModel = $this->magentoProductFactory->create();
            $this->magentoProductModel->setProductId($this->getMagentoProductId());
        }

        return $this->prepareMagentoProduct($this->magentoProductModel);
    }

    /**
     * @param \M2E\Kaufland\Model\Magento\Product\Cache $instance
     */
    public function setMagentoProduct(\M2E\Kaufland\Model\Magento\Product\Cache $instance): void
    {
        $this->magentoProductModel = $this->prepareMagentoProduct($instance);
    }

    protected function prepareMagentoProduct(
        \M2E\Kaufland\Model\Magento\Product\Cache $instance
    ): \M2E\Kaufland\Model\Magento\Product\Cache {
        $instance->setStoreId($this->getListing()->getStoreId());
        $instance->setStatisticId($this->getId());

        return $instance;
    }

    // ----------------------------------------

    public function getAccount(): Account
    {
        return $this->getListing()->getAccount();
    }

    // ----------------------------------------

    public function getListingId(): int
    {
        return (int)$this->getData('listing_id');
    }

    public function getMagentoProductId(): int
    {
        return (int)$this->getData('magento_product_id');
    }

    public function getUnitId(): int
    {
        return (int)$this->getData('unit_id');
    }

    // ---------------------------------------

    public function isStatusChangerUser(): bool
    {
        return $this->getStatusChanger() === self::STATUS_CHANGER_USER;
    }

    public function getStatusChanger(): int
    {
        return (int)$this->getData('status_changer');
    }

    // ---------------------------------------

    public function setAdditionalData(array $data): self
    {
        $this->setData(ProductResource::COLUMN_ADDITIONAL_DATA, json_encode($data));

        return $this;
    }

    /**
     * @return array
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getAdditionalData(): array
    {
        return $this->getSettings(ProductResource::COLUMN_ADDITIONAL_DATA);
    }

    public function setChannelProductEmptyAttributes(array $data): self
    {
        $this->setData(ProductResource::COLUMN_CHANNEL_PRODUCT_EMPTY_ATTRIBUTES, json_encode($data));

        return $this;
    }

    //########################################

    public function isStatusNotListed(): bool
    {
        return $this->getStatus() === self::STATUS_NOT_LISTED;
    }

    // ---------------------------------------

    public function isStatusListed(): bool
    {
        return $this->getStatus() === self::STATUS_LISTED;
    }

    public function isStatusInactive(): bool
    {
        return $this->getStatus() === self::STATUS_INACTIVE;
    }

    public function setStatusListed(int $changer): self
    {
        $this->setStatus(self::STATUS_LISTED, $changer);

        return $this;
    }

    public function setStatusInactive(int $changer): self
    {
        $this->setStatus(self::STATUS_INACTIVE, $changer);

        return $this;
    }

    public function setStatus(int $status, int $changer): self
    {
        $this->setData(ProductResource::COLUMN_STATUS, $status);
        $this->setStatusChanger($changer);
        $this->setStatusChangeDate(\M2E\Core\Helper\Date::createCurrentGmt());

        return $this;
    }

    public function getStatus(): int
    {
        return (int)$this->getData('status');
    }

    public function makeIncomplete(array $channelProductEmptyAttributes): void
    {
        $this->setData(ProductResource::COLUMN_IS_INCOMPLETE, 1);
        $this->setChannelProductEmptyAttributes($channelProductEmptyAttributes);
    }

    public function makeComplete(): void
    {
        $this->setData(ProductResource::COLUMN_IS_INCOMPLETE, 0);
        $this->setChannelProductEmptyAttributes([]);
    }

    public function isIncomplete(): bool
    {
        return (bool)$this->getData(ProductResource::COLUMN_IS_INCOMPLETE);
    }

    public function getStatusChangeDate(): ?\DateTimeImmutable
    {
        $value = $this->getData(ProductResource::COLUMN_STATUS_CHANGE_DATE);
        if (empty($value)) {
            return null;
        }

        return \DateTimeImmutable::createFromMutable(\M2E\Core\Helper\Date::createDateGmt($value));
    }

    private function setStatusChangeDate(\DateTime $date): self
    {
        $this->setData(ProductResource::COLUMN_STATUS_CHANGE_DATE, $date->format('Y-m-d H:i:s'));

        return $this;
    }

    // ---------------------------------------

    public function isListable(): bool
    {
        return $this->isStatusNotListed();
    }

    public function isListableAsProduct(): bool
    {
        return $this->isListable()
            && $this->isKauflandProductCreator()
            && !$this->hasKauflandProductId()
            && $this->getListing()->hasDescriptionPolicy()
            && $this->hasCategory();
    }

    public function isListableAsUnit(): bool
    {
        return $this->isListable()
            && $this->hasKauflandProductId();
    }

    public function isRelistable(): bool
    {
        return $this->isStatusInactive();
    }

    public function isRevisable(): bool
    {
        return $this->isStatusListed();
    }

    public function isRevisableAsProduct(): bool
    {
        return $this->isRevisable()
            && $this->isReadyForReviseAsProduct()
            && $this->isEnableReviseSyncRuleForProduct();
    }

    public function isReadyForReviseAsProduct(): bool
    {
        return $this->getListing()->hasDescriptionPolicy()
            && $this->hasCategory();
    }

    public function isRevisableAsUnit(): bool
    {
        return $this->isRevisable() && $this->hasKauflandProductId();
    }

    public function isStoppable(): bool
    {
        return $this->isStatusListed();
    }

    public function isRetirable(): bool
    {
        return ($this->isStatusListed() || $this->isStatusInactive());
    }

    // ----------------------------------------

    public function canBeForceDeleted($value = null)
    {
        if ($value === null) {
            return $this->canBeForceDeleted;
        }

        $this->canBeForceDeleted = $value;

        return $this;
    }

    // ----------------------------------------

    public function getKauflandOfferId(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_OFFER_ID);
    }

    public function getKauflandProductId(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_KAUFLAND_PRODUCT_ID);
    }

    public function hasKauflandProductId(): bool
    {
        return $this->getData(ProductResource::COLUMN_KAUFLAND_PRODUCT_ID) !== null;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getSellingFormatTemplate(): \M2E\Kaufland\Model\Template\SellingFormat
    {
        return $this->getListing()->getTemplateSellingFormat();
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getSynchronizationTemplate(): \M2E\Kaufland\Model\Template\Synchronization
    {
        return $this->getListing()->getTemplateSynchronization();
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getDescriptionTemplate(): \M2E\Kaufland\Model\Template\Description
    {
        return $this->getListing()->getTemplateDescription();
    }

    /**
     * @return \M2E\Kaufland\Model\Policy\ShippingDataProvider
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getShippingPolicyDataProvider(): Policy\ShippingDataProvider
    {
        return $this->shippingDataProviderFactory->createShipping($this->getShippingTemplate(), $this);
    }

    /**
     * @return \M2E\Kaufland\Model\Product\SkuGenerator
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getSkuGenerator(): \M2E\Kaufland\Model\Product\SkuGenerator
    {
        return $this->skuGeneratorFactory->create($this, $this->getListing()->getSkuSettings());
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getSellingFormatTemplateSource(): \M2E\Kaufland\Model\Template\SellingFormat\Source
    {
        return $this->getSellingFormatTemplate()->getSource($this->getMagentoProduct());
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getDescriptionTemplateSource(): \M2E\Kaufland\Model\Template\Description\Source
    {
        return $this->getDescriptionTemplate()->getSource($this->getMagentoProduct());
    }

    public function getRenderedDescription(): string
    {
        return $this->descriptionRendererFactory
            ->create($this)
            ->parseTemplate($this->getDescriptionTemplateSource()->getDescription());
    }

    public static function createOnlineDescription(string $description): string
    {
        return \M2E\Core\Helper\Data::md5String($description);
    }

    // ---------------------------------------

    /**
     * @return float
     */
    public function getOnlineCurrentPrice(): float
    {
        return (float)$this->getData(ProductResource::COLUMN_ONLINE_PRICE);
    }

    public function getOnlineQty(): int
    {
        return (int)$this->getData(ProductResource::COLUMN_ONLINE_QTY);
    }

    public function getOnlineHandlingTime(): int
    {
        return (int)$this->getData(ProductResource::COLUMN_ONLINE_HANDLING_TIME);
    }

    public function getOnlineShippingGroupId(): int
    {
        return (int)$this->getData(ProductResource::COLUMN_ONLINE_SHIPPING_GROUP_ID);
    }

    public function getOnlineWarehouseId(): int
    {
        return (int)$this->getData(ProductResource::COLUMN_ONLINE_WAREHOUSE_ID);
    }

    public function getOnlineCondition(): ?string
    {
        return $this->getData(ProductResource::COLUMN_ONLINE_CONDITION);
    }

    public function getOnlineCategoryId()
    {
        return $this->getData(ProductResource::COLUMN_ONLINE_CATEGORY_ID);
    }

    public function getOnlineTitle(): ?string
    {
        return $this->getData(ProductResource::COLUMN_ONLINE_TITLE);
    }

    public function getOnlineDescription(): ?string
    {
        return $this->getData(ProductResource::COLUMN_ONLINE_DESCRIPTION);
    }

    public function getOnlineImage(): ?string
    {
        return $this->getData(ProductResource::COLUMN_ONLINE_IMAGE);
    }

    // ---------------------------------------

    /**
     * @return float|int
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getFixedPrice()
    {
        $src = $this->getSellingFormatTemplate()->getFixedPriceSource();
        $priceModifier = $this->getSellingFormatTemplate()->getFixedPriceModifier();

        return $this->getCalculatedPriceWithModifier(
            $src,
            $priceModifier,
        );
    }

    private function getCalculatedPriceWithModifier($src, $modifier)
    {
        $calculator = $this->priceCalculatorFactory->create();
        $calculator->setProduct($this);
        $calculator->setSource($src);
        $calculator->setModifier($modifier);

        return $calculator->getProductValue();
    }

    public function getQty(bool $magentoMode = false): int
    {
        $calculator = $this->qtyCalculatorFactory->create($this);
        $calculator->setIsMagentoMode($magentoMode);

        return $calculator->getProductValue();
    }

    //########################################

    public function changeListing(\M2E\Kaufland\Model\Listing $listing): self
    {
        $this->setListingId($listing->getId());
        $this->initListing($listing);

        return $this;
    }

    private function setListingId(int $listingId): self
    {
        $this->setData(ProductResource::COLUMN_LISTING_ID, $listingId);

        return $this;
    }

    private function setMagentoProductId(int $magentoProductId): self
    {
        $this->setData(ProductResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return $this;
    }

    public function setKauflandProductId(string $productId): self
    {
        $this->setData(ProductResource::COLUMN_KAUFLAND_PRODUCT_ID, $productId);

        return $this;
    }

    public function setKauflandOfferId(string $offerId): self
    {
        $this->setData(ProductResource::COLUMN_OFFER_ID, $offerId);

        return $this;
    }

    public function resetKauflandOfferId(): self
    {
        $this->setData(ProductResource::COLUMN_OFFER_ID, null);

        return $this;
    }

    public function setUnitId(int $unitId): self
    {
        $this->setData(ProductResource::COLUMN_UNIT_ID, $unitId);

        return $this;
    }

    public function setStoreFrontId(int $storefrontId): self
    {
        $this->setData(ProductResource::COLUMN_STOREFRONT_ID, $storefrontId);

        return $this;
    }

    public function setOnlinePrice(float $onlinePrice): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_PRICE, $onlinePrice);

        return $this;
    }

    public function setOnlineQty(int $onlineQty): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_QTY, $onlineQty);

        return $this;
    }

    public function setOnlineCondition(string $condition): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_CONDITION, $condition);

        return $this;
    }

    public function setOnlineHandlingTime(int $handlingTime): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_HANDLING_TIME, $handlingTime);

        return $this;
    }

    public function setOnlineWarehouse(int $warehouseId): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_WAREHOUSE_ID, $warehouseId);

        return $this;
    }

    public function setOnlineShippingGroupId(int $shippingGroupId): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_SHIPPING_GROUP_ID, $shippingGroupId);

        return $this;
    }

    public function setOnlineCategoryId(int $categoryId): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_CATEGORY_ID, $categoryId);

        return $this;
    }

    public function setOnlineCategoryData(string $mainCategoryData): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_CATEGORIES_DATA, $mainCategoryData);

        return $this;
    }

    public function setOnlineCategoryAttributesData(string $categoryAttributesData): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_CATEGORIES_ATTRIBUTES_DATA, $categoryAttributesData);

        return $this;
    }

    public function getOnlineCategoryAttributesData(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_ONLINE_CATEGORIES_ATTRIBUTES_DATA);
    }

    public function hasCategory(): bool
    {
        return $this->getTemplateCategoryId() !== null;
    }

    public function setTemplateCategoryId(int $categoryId): void
    {
        $this->setData(ProductResource::COLUMN_TEMPLATE_CATEGORY_ID, $categoryId);
    }

    public function getTemplateCategoryId(): ?int
    {
        $categoryId = $this->getData(ProductResource::COLUMN_TEMPLATE_CATEGORY_ID);
        return $categoryId !== null ? (int)$categoryId : null;
    }

    public function hasCategoryTemplate(): bool
    {
        return !empty($this->getData(ProductResource::COLUMN_TEMPLATE_CATEGORY_ID));
    }

    /**
     * @return \M2E\Kaufland\Model\Category\Dictionary
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getCategoryDictionary(): Category\Dictionary
    {
        if (isset($this->categoryDictionary)) {
            return $this->categoryDictionary;
        }

        if (!$this->hasCategoryTemplate()) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Category was not selected.');
        }

        return $this->categoryDictionary = $this->categoryDictionaryRepository->get($this->getTemplateCategoryId());
    }

    public function isKauflandProductCreator(): bool
    {
        return (bool)$this->getData(ProductResource::COLUMN_IS_KAUFLAND_PRODUCT_CREATOR);
    }

    public function setKauflandProductCreator(bool $value): void
    {
        $this->setData(ProductResource::COLUMN_IS_KAUFLAND_PRODUCT_CREATOR, (int)$value);
    }

    public function setOnlineTitle(string $title): void
    {
        $this->setData(ProductResource::COLUMN_ONLINE_TITLE, $title);
    }

    public function setOnlineDescription(string $description): void
    {
        $this->setData(ProductResource::COLUMN_ONLINE_DESCRIPTION, $description);
    }

    public function setOnlineImages(string $image): void
    {
        $this->setData(ProductResource::COLUMN_ONLINE_IMAGE, $image);
    }

    private function setStatusChanger(int $statusChanger): void
    {
        $this->validateStatusChanger($statusChanger);

        $this->setData(ProductResource::COLUMN_STATUS_CHANGER, $statusChanger);
    }

    // ----------------------------------------

    public static function getStatusTitle(int $status): string
    {
        $statuses = [
            self::STATUS_NOT_LISTED => (string)__('Not Listed'),
            self::STATUS_LISTED => (string)__('Active'),
            self::STATUS_INACTIVE => (string)__('Inactive'),
        ];

        return $statuses[$status] ?? 'Unknown';
    }

    public function getProductStatusTitle(): string
    {
        if ($this->isIncomplete()) {
            return self::getIncompleteStatusTitle();
        }

        return self::getStatusTitle($this->getStatus());
    }

    public static function getIncompleteStatusTitle(): string
    {
        return (string)__('Incomplete');
    }

    private function isEnableReviseSyncRuleForProduct(): bool
    {
        $synchronizationTemplate = $this->getSynchronizationTemplate();

        return $synchronizationTemplate->isReviseUpdateTitle()
            || $synchronizationTemplate->isReviseUpdateDescription()
            || $synchronizationTemplate->isReviseUpdateImages()
            || $synchronizationTemplate->isReviseUpdateCategories();
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function getShippingTemplate(): \M2E\Kaufland\Model\Template\Shipping
    {
        return $this->getListing()->getTemplateShipping();
    }

    public function hasBlockingByError(): bool
    {
        $rawDate = $this->getData(ProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE);
        if (empty($rawDate)) {
            return false;
        }

        $lastBlockingDate = \M2E\Core\Helper\Date::createDateGmt($rawDate);
        $twentyFourHoursAgoDate = \M2E\Core\Helper\Date::createCurrentGmt()->modify('-24 hour');

        return $lastBlockingDate->getTimestamp() > $twentyFourHoursAgoDate->getTimestamp();
    }

    public function removeBlockingByError(): self
    {
        $this->setData(ProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE, null);

        return $this;
    }

    private function validateStatusChanger(int $changer): void
    {
        $allowed = [
            self::STATUS_CHANGER_SYNCH,
            self::STATUS_CHANGER_USER,
            self::STATUS_CHANGER_COMPONENT,
            self::STATUS_CHANGER_OBSERVER
        ];

        if (!in_array($changer, $allowed)) {
            throw new \M2E\Kaufland\Model\Exception\Logic(sprintf('Status changer %s not valid.', $changer));
        }
    }
}
