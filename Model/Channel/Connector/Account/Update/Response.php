<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Connector\Account\Update;

class Response
{
    private string $identifier;

    /** @var \M2E\Kaufland\Model\Channel\Storefront\Item[] */
    private array $storefronts;

    /** @var \M2E\Kaufland\Model\Channel\Warehouse\Item[] */
    private array $warehouses;

    /** @var \M2E\Kaufland\Model\Channel\ShippingGroup\Item[] */
    private array $shippingGroups;

    public function __construct(string $identifier, array $storefronts, array $warehouses, array $shippingGroups)
    {
        $this->identifier = $identifier;
        $this->storefronts = $storefronts;
        $this->warehouses = $warehouses;
        $this->shippingGroups = $shippingGroups;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return \M2E\Kaufland\Model\Channel\Storefront\Item[]
     */
    public function getStorefronts(): array
    {
        return $this->storefronts;
    }

    /**
     * @return \M2E\Kaufland\Model\Channel\Warehouse\Item[]
     */
    public function getWarehouses(): array
    {
        return $this->warehouses;
    }

    /**
     * @return \M2E\Kaufland\Model\Channel\ShippingGroup\Item[]
     */
    public function getShippingGroups(): array
    {
        return $this->shippingGroups;
    }
}
