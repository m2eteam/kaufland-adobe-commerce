<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Warehouse;

class Item
{
    private int $warehouseId;
    private string $name;
    private bool $isDefault;
    private string $type;
    private array $address;

    public function __construct(
        int $warehouseId,
        string $name,
        bool $isDefault,
        string $type,
        array $address
    ) {
        $this->warehouseId = $warehouseId;
        $this->name = $name;
        $this->isDefault = $isDefault;
        $this->type = $type;
        $this->address = $address;
    }

    public function getWarehouseId(): int
    {
        return $this->warehouseId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAddress(): array
    {
        return $this->address;
    }
}
