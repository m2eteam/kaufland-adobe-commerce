<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Warehouse\Get;

class Response
{
    /** @var \M2E\Kaufland\Model\Kaufland\Connector\Account\Warehouse[] */
    private array $warehouses;

    public function __construct(array $warehouses)
    {
        $this->warehouses = $warehouses;
    }

    /**
     * @return \M2E\Kaufland\Model\Kaufland\Connector\Account\Warehouse[]
     */
    public function getWarehouses(): array
    {
        return $this->warehouses;
    }
}
