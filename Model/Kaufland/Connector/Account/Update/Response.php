<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Account\Update;

class Response
{
    private string $identifier;

    /** @var \M2E\Kaufland\Model\Kaufland\Connector\Account\Storefront[] */
    private array $storefronts;

    /** @var \M2E\Kaufland\Model\Kaufland\Connector\Account\Warehouse[] */
    private array $warehouses;

    /** @var \M2E\Kaufland\Model\Kaufland\Connector\Account\ShippingGroup[] */
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
     * @return \M2E\Kaufland\Model\Kaufland\Connector\Account\Storefront[]
     */
    public function getStorefronts(): array
    {
        return $this->storefronts;
    }

    /**
     * @return \M2E\Kaufland\Model\Kaufland\Connector\Account\Warehouse[]
     */
    public function getWarehouses(): array
    {
        return $this->warehouses;
    }

    /**
     * @return \M2E\Kaufland\Model\Kaufland\Connector\Account\ShippingGroup[]
     */
    public function getShippingGroups(): array
    {
        return $this->shippingGroups;
    }
}
