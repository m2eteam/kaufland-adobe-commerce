<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Other;

class KauflandProduct
{
    /**
     * Find supported product statuses by the link below; path: /Schemas/UnitStatuses.
     * @link https://sellerapi.kaufland.com/?page=endpoints
     */
    private const PRODUCT_STATUS_AVAILABLE = 'AVAILABLE';
    private const PRODUCT_STATUS_ONHOLD = 'ONHOLD';

    private int $accountId;
    private int $storefrontId;
    private string $productId;
    private int $status;
    private ?string $title;
    private string $currencyCode;
    private float $price;
    private int $qty;
    private ?int $categoryId;
    private int $unitId;
    private string $offerId;
    private array $eans;
    private ?string $mainPicture;
    private ?string $categoryTitle;
    private bool $fulfilledByMerchant;
    private int $warehouseId;
    private int $shippingGroupId;
    private ?string $condition;
    private int $handlingTime;
    private bool $isValid;
    private array $channelProductEmptyAttributes;

    public function __construct(
        int $accountId,
        int $storefrontId,
        int $unitId,
        string $offerId,
        string $productId,
        int $status,
        ?string $title,
        array $eans,
        string $currencyCode,
        float $price,
        int $qty,
        ?string $mainPicture,
        ?int $categoryId,
        ?string $categoryTitle,
        bool $fulfilledByMerchant,
        int $warehouseId,
        int $shippingGroupId,
        ?string $condition,
        int $handlingTime,
        bool $isValid,
        array $channelProductEmptyAttributes
    ) {
        $this->accountId = $accountId;
        $this->storefrontId = $storefrontId;
        $this->productId = $productId;
        $this->status = $status;
        $this->title = $title;
        $this->currencyCode = $currencyCode;
        $this->price = $price;
        $this->qty = $qty;
        $this->unitId = $unitId;
        $this->offerId = $offerId;
        $this->eans = $eans;
        $this->mainPicture = $mainPicture;
        $this->categoryId = $categoryId;
        $this->categoryTitle = $categoryTitle;
        $this->fulfilledByMerchant = $fulfilledByMerchant;
        $this->warehouseId = $warehouseId;
        $this->shippingGroupId = $shippingGroupId;
        $this->condition = $condition;
        $this->handlingTime = $handlingTime;
        $this->isValid = $isValid;
        $this->channelProductEmptyAttributes = $channelProductEmptyAttributes;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getStorefrontId(): int
    {
        return $this->storefrontId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    public function getUnitId(): int
    {
        return $this->unitId;
    }

    public function getOfferId(): ?string
    {
        return $this->offerId;
    }

    public function getEans(): array
    {
        return $this->eans;
    }

    public function getMainPicture(): ?string
    {
        return $this->mainPicture;
    }

    public function getCategoryTitle(): ?string
    {
        return $this->categoryTitle;
    }

    public function getFulfilledByMerchant(): bool
    {
        return $this->fulfilledByMerchant;
    }

    // ----------------------------------------

    public function getWarehouseId(): int
    {
        return $this->warehouseId;
    }

    public function getShippingGroupId(): int
    {
        return $this->shippingGroupId;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function getHandlingTime(): int
    {
        return $this->handlingTime;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getChannelProductEmptyAttributes(): array
    {
        return $this->channelProductEmptyAttributes;
    }

    public static function convertChannelStatusToExtension(string $channelStatus): int
    {
        if ($channelStatus === self::PRODUCT_STATUS_AVAILABLE) {
            return \M2E\Kaufland\Model\Product::STATUS_LISTED;
        }

        if ($channelStatus === self::PRODUCT_STATUS_ONHOLD) {
            return \M2E\Kaufland\Model\Product::STATUS_INACTIVE;
        }

        throw new \M2E\Kaufland\Model\Exception\Logic(
            (string)__('Status for %channelStatus not defined.', ['channelStatus' => $channelStatus]),
        );
    }
}
