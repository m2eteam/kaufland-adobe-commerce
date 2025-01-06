<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\OperationHistory;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\OperationHistory $resource;
    private \M2E\Kaufland\Model\OperationHistoryFactory $operationHistoryFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\OperationHistory $resource,
        \M2E\Kaufland\Model\OperationHistoryFactory $operationHistoryFactory
    ) {
        $this->resource = $resource;
        $this->operationHistoryFactory = $operationHistoryFactory;
    }

    public function get(int $id): \M2E\Kaufland\Model\OperationHistory
    {
        $model = $this->operationHistoryFactory->create();
        $this->resource->load($model, $id);
        if ($model->isObjectNew()) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Entity not found by id ' . $id);
        }

        return $model;
    }

    public function clear(\DateTime $borderDate): void
    {
        $minDate = $borderDate->format('Y-m-d H:i:s');

        $this->resource->getConnection()->delete($this->resource->getMainTable(), "start_date <= '$minDate'");
    }
}
