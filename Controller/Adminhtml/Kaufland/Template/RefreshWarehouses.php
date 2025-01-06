<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

class RefreshWarehouses extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractTemplate
{
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\Warehouse\SynchronizeService $warehouseSynchronizeService;
    private \M2E\Kaufland\Model\Warehouse\Repository $warehouseRepository;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Warehouse\SynchronizeService $warehouseSynchronizeService,
        \M2E\Kaufland\Model\Kaufland\Template\Manager $templateManager,
        \M2E\Kaufland\Model\Warehouse\Repository $warehouseRepository
    ) {
        parent::__construct($templateManager);
        $this->accountRepository = $accountRepository;
        $this->warehouseSynchronizeService = $warehouseSynchronizeService;
        $this->warehouseRepository = $warehouseRepository;
    }

    public function execute()
    {
        $accounts = $this->accountRepository->getAll();

        foreach ($accounts as $account) {
            $this->warehouseSynchronizeService->updateWarehouses($account);
        }

        $warehouses = $this->warehouseRepository->getAll();

        $arrayWarehouses = [];
          /** @var \M2E\Kaufland\Model\Warehouse $warehouse */
        foreach ($warehouses as $warehouse) {
            $arrayWarehouses[] = [
                'warehouse_id' => $warehouse->getId(),
                'name' => $warehouse->getName(),
            ];
        }

        $this->setJsonContent($arrayWarehouses);

        return $this->getResult();
    }
}
