<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Connector\Warehouse\Get;

class Response
{
    /** @var \M2E\Kaufland\Model\Channel\Warehouse\Item[] */
    private array $warehouses;

    public function __construct(array $warehouses)
    {
        $this->warehouses = $warehouses;
    }

    /**
     * @return \M2E\Kaufland\Model\Channel\Warehouse\Item[]
     */
    public function getWarehouses(): array
    {
        return $this->warehouses;
    }
}
