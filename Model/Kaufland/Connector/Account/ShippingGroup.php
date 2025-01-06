<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Account;

class ShippingGroup
{
    private int $shippingGroupId;
    private string $storefront;
    private string $name;
    private string $type;
    private bool $isDefault;
    private string $currency;
    private array $regions;

    public function __construct(
        int $shippingGroupId,
        string $storefront,
        string $name,
        bool $isDefault,
        string $type,
        string $currency,
        array $regions
    ) {
        $this->shippingGroupId = $shippingGroupId;
        $this->storefront = $storefront;
        $this->name = $name;
        $this->type = $type;
        $this->isDefault = $isDefault;
        $this->currency = $currency;
        $this->regions = $regions;
    }

    public function getShippingGroupId(): int
    {
        return $this->shippingGroupId;
    }

    public function getStorefront(): string
    {
        return $this->storefront;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getRegions(): array
    {
        return $this->regions;
    }
}
