<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing;

use M2E\Kaufland\Model\ResourceModel\Listing\Other as ListingOtherResource;

class Other extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    private ?\M2E\Kaufland\Model\Account $accountModel;
    private \M2E\Kaufland\Model\Magento\Product\Cache $magentoProductModel;

    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;
    private \M2E\Kaufland\Model\Magento\Product\CacheFactory $productCacheFactory;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \M2E\Kaufland\Model\Magento\Product\CacheFactory $productCacheFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $modelFactory,
            $activeRecordFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->accountRepository = $accountRepository;
        $this->storefrontRepository = $storefrontRepository;
        $this->productCacheFactory = $productCacheFactory;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\Kaufland\Model\ResourceModel\Listing\Other::class);
    }

    public function init(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront,
        int $unitId,
        ?string $offerId,
        string $kauflandProductId,
        int $status,
        string $title,
        array $eans,
        string $currencyCode,
        float $price,
        int $qty,
        string $mainPicture,
        int $categoryId,
        ?string $categoryTitle,
        bool $fulfilledByMerchant,
        int $warehouseId,
        int $shippingGroupId,
        string $condition,
        int $handlingTime
    ) {
        $now = \M2E\Core\Helper\Date::createCurrentGmt();
        $this
            ->setData(ListingOtherResource::COLUMN_ACCOUNT_ID, $account->getId())
            ->setData(ListingOtherResource::COLUMN_STOREFRONT_ID, $storefront->getId())
            ->setData(ListingOtherResource::COLUMN_UNIT_ID, $unitId)
            ->setData(ListingOtherResource::COLUMN_OFFER_ID, $offerId)
            ->setData(ListingOtherResource::COLUMN_KAUFLAND_PRODUCT_ID, $kauflandProductId)
            ->setData(ListingOtherResource::COLUMN_STATUS, $status)
            ->setData(ListingOtherResource::COLUMN_TITLE, $title)
            ->setData(ListingOtherResource::COLUMN_EANS, json_encode($eans, JSON_THROW_ON_ERROR))
            ->setData(ListingOtherResource::COLUMN_CURRENCY_CODE, $currencyCode)
            ->setData(ListingOtherResource::COLUMN_PRICE, $price)
            ->setData(ListingOtherResource::COLUMN_QTY, $qty)
            ->setData(ListingOtherResource::COLUMN_MAIN_PICTURE, $mainPicture)
            ->setData(ListingOtherResource::COLUMN_CATEGORY_ID, $categoryId)
            ->setData(ListingOtherResource::COLUMN_WAREHOUSE_ID, $warehouseId)
            ->setData(ListingOtherResource::COLUMN_SHIPPING_GROUP_ID, $shippingGroupId)
            ->setData(ListingOtherResource::COLUMN_CONDITION, $condition)
            ->setData(ListingOtherResource::COLUMN_HANDLING_TIME, $handlingTime)
            ->setData(ListingOtherResource::COLUMN_CATEGORY_TITLE, ($categoryTitle === 'N/A') ? null : $categoryTitle)
            ->setData(ListingOtherResource::COLUMN_FULFILLED_BY_MERCHANT, (int)$fulfilledByMerchant)
            ->setData(ListingOtherResource::COLUMN_UPDATE_DATE, $now->format('Y-m-d H:i:s'))
            ->setData(ListingOtherResource::COLUMN_CREATE_DATE, $now->format('Y-m-d H:i:s'));

        return $this;
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_ACCOUNT_ID);
    }

    public function setAccountId(int $accountId): void
    {
        $this->setData(ListingOtherResource::COLUMN_ACCOUNT_ID, $accountId);
    }

    public function getStorefrontId(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_STOREFRONT_ID);
    }

    public function getStorefront(): \M2E\Kaufland\Model\Storefront
    {
        $storefrontId = $this->getStorefrontId();

        return $this->storefrontRepository->get($storefrontId);
    }

    public function getUnitId(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_UNIT_ID);
    }

    public function setUnitId(int $unitId): void
    {
        $this->setData(ListingOtherResource::COLUMN_UNIT_ID, $unitId);
    }

    public function getOfferId(): ?string
    {
        return $this->getData(ListingOtherResource::COLUMN_OFFER_ID);
    }

    public function setOfferId(string $offerId): void
    {
        $this->setData(ListingOtherResource::COLUMN_OFFER_ID, $offerId);
    }

    public function getKauflandProductId(): string
    {
        return (string)$this->getData(ListingOtherResource::COLUMN_KAUFLAND_PRODUCT_ID);
    }

    public function setKauflandProductId(string $kauflandProductId): void
    {
        $this->setData(ListingOtherResource::COLUMN_KAUFLAND_PRODUCT_ID, $kauflandProductId);
    }

    public function getStatus(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_STATUS);
    }

    public function setStatus(int $status): self
    {
        $this->setData(ListingOtherResource::COLUMN_STATUS, $status);

        return $this;
    }

    public function getTitle(): string
    {
        return (string)$this->getData(ListingOtherResource::COLUMN_TITLE);
    }

    public function setTitle(string $title): void
    {
        $this->setData(ListingOtherResource::COLUMN_TITLE, $title);
    }

    public function setEans(array $eans): void
    {
        $this->setData(ListingOtherResource::COLUMN_EANS, json_encode($eans, JSON_THROW_ON_ERROR));
    }

    public function getEans(): array
    {
        $json = $this->getData(ListingOtherResource::COLUMN_EANS);
        if ($json === null) {
            return [];
        }

        return json_decode($json, true);
    }

    public function getCurrency(): string
    {
        return (string)$this->getData(ListingOtherResource::COLUMN_CURRENCY_CODE);
    }

    public function setCurrency(string $currency): void
    {
        $this->setData(ListingOtherResource::COLUMN_CURRENCY_CODE, $currency);
    }

    public function getPrice(): float
    {
        return (float)$this->getData(ListingOtherResource::COLUMN_PRICE);
    }

    public function setPrice(float $onlinePrice): void
    {
        $this->setData(ListingOtherResource::COLUMN_PRICE, $onlinePrice);
    }

    public function getQty(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_QTY);
    }

    public function setQty(int $qty): void
    {
        $this->setData(ListingOtherResource::COLUMN_QTY, $qty);
    }

    public function getMainPicture(): string
    {
        return (string)$this->getData(ListingOtherResource::COLUMN_TITLE);
    }

    public function setMainPicture(string $mainPicture): void
    {
        $this->setData(ListingOtherResource::COLUMN_MAIN_PICTURE, $mainPicture);
    }

    public function getCategoryId(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_CATEGORY_ID);
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->setData(ListingOtherResource::COLUMN_CATEGORY_ID, $categoryId);
    }

    public function getCategoryTitle(): string
    {
        $categoryTitle = $this->getData(ListingOtherResource::COLUMN_CATEGORY_TITLE);

        if ($categoryTitle === null) {
            return 'N/A';
        }

        return (string)$categoryTitle;
    }

    public function setCategoryTitle(?string $categoryTitle): void
    {
        if ($categoryTitle === 'N/A') {
            $this->setData(ListingOtherResource::COLUMN_CATEGORY_TITLE, null);

            return;
        }
        $this->setData(ListingOtherResource::COLUMN_CATEGORY_TITLE, $categoryTitle);
    }

    public function getFulfilledByMerchant(): bool
    {
        return (bool)$this->getData(ListingOtherResource::COLUMN_FULFILLED_BY_MERCHANT);
    }

    public function setFulfilledByMerchant(bool $fulfilledByMerchant): void
    {
        $this->setData(ListingOtherResource::COLUMN_FULFILLED_BY_MERCHANT, (int)$fulfilledByMerchant);
    }

    public function getProductIdUnitIdKey(): string
    {
        return $this->getKauflandProductId() . $this->getUnitId();
    }

    public function delete()
    {
        parent::delete();

        $this->accountModel = null;
        unset($this->magentoProductModel);

        return $this;
    }

    //########################################

    public function getAccount(): \M2E\Kaufland\Model\Account
    {
        if (!isset($this->accountModel)) {
            $this->accountModel = $this->accountRepository->get($this->getAccountId());
        }

        return $this->accountModel;
    }

    // ---------------------------------------

    public function getMagentoProduct(): \M2E\Kaufland\Model\Magento\Product\Cache
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->magentoProductModel)) {
            return $this->magentoProductModel;
        }

        if (!$this->hasMagentoProductId()) {
            throw new \M2E\Kaufland\Model\Exception('Product id is not set');
        }

        return $this->magentoProductModel = $this->productCacheFactory->create()
                                                                      ->setStoreId($this->getRelatedStoreId())
                                                                      ->setProductId($this->getMagentoProductId());
    }

    public function hasMagentoProductId(): bool
    {
        return $this->getMagentoProductId() !== null;
    }

    public function getMagentoProductId(): ?int
    {
        $productId = $this->getData(ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID);
        if ($productId === null) {
            return null;
        }

        return (int)$productId;
    }

    // ---------------------------------------

    public function mapMagentoProduct(int $magentoProductId): void
    {
        $this->setData(ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);
    }

    public function unmapMagentoProduct(): void
    {
        $this->setData(ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID);
    }

    // ---------------------------------------

    public function setMovedToListingProductId(?int $id): void
    {
        $this->setData(ListingOtherResource::COLUMN_MOVED_TO_LISTING_PRODUCT_ID, $id);
    }

    public function getRelatedStoreId(): int
    {
        return $this->getAccount()->getUnmanagedListingSettings()->getRelatedStoreId();
    }
}
