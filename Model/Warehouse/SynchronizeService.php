<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Warehouse;

class SynchronizeService
{
    private Repository $repository;
    private \M2E\Kaufland\Model\WarehouseFactory $warehouseFactory;
    /** @var \M2E\Kaufland\Model\Warehouse\Get */
    private Get $getWarehouse;

    public function __construct(
        \M2E\Kaufland\Model\WarehouseFactory $warehouseFactory,
        \M2E\Kaufland\Model\Warehouse\Get $getWarehouse,
        Repository $repository
    ) {
        $this->repository = $repository;
        $this->warehouseFactory = $warehouseFactory;
        $this->getWarehouse = $getWarehouse;
    }

    /**
     * @param \M2E\Kaufland\Model\Account $account
     * @param \M2E\Kaufland\Model\Channel\Warehouse\Item[] $warehouses
     *
     * @return void
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function sync(\M2E\Kaufland\Model\Account $account, array $warehouses): void
    {
        /** @var \M2E\Kaufland\Model\Warehouse[] $exists */
        $exists = [];
        foreach ($account->getWarehouses() as $warehouse) {
            $exists[$warehouse->getWarehouseId()] = $warehouse;
        }

        foreach ($warehouses as $responseWarehouse) {
            if (isset($exists[$responseWarehouse->getWarehouseId()])) {
                $exist = $exists[$responseWarehouse->getWarehouseId()];

                if (
                    $responseWarehouse->getName() !== $exist->getName()
                    || $responseWarehouse->isDefault() !== $exist->isDefault()
                    || $responseWarehouse->getType() !== $exist->getType()
                ) {
                    $exist->setName($responseWarehouse->getName())
                          ->setIsDefault($responseWarehouse->isDefault())
                          ->setType($responseWarehouse->getType());

                    $this->repository->save($exist);
                }

                continue;
            }

            $warehouse = $this->warehouseFactory->create();
            $warehouse->create(
                $account,
                $responseWarehouse->getWarehouseId(),
                $responseWarehouse->getName(),
                $responseWarehouse->getType(),
                $responseWarehouse->isDefault(),
                $responseWarehouse->getAddress(),
            );

            $this->repository->create($warehouse);

            $exists[$warehouse->getWarehouseId()] = $warehouse;
        }

        $account->setWarehouses(array_values($exists));
    }

    public function updateWarehouses(\M2E\Kaufland\Model\Account $account)
    {
        $warehouses = $this->getWarehouse->getWarehouses($account);
        $this->sync($account, $warehouses);
    }
}
