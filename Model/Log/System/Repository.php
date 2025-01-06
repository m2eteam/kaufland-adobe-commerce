<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Log\System;

use M2E\Kaufland\Model\ResourceModel\Log\System as LogSystemResource;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\Log\System $resource;
    private \M2E\Kaufland\Model\ResourceModel\Log\System\CollectionFactory $collectionFactory;
    private \M2E\Kaufland\Model\Log\SystemFactory $logSystemFactory;

    public function __construct(
        \M2E\Kaufland\Model\Log\SystemFactory $logSystemFactory,
        \M2E\Kaufland\Model\ResourceModel\Log\System $resource,
        \M2E\Kaufland\Model\ResourceModel\Log\System\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->logSystemFactory = $logSystemFactory;
    }

    public function create(int $type, string $class, string $message, string $details, array $additionalData = []): void
    {
        $log = $this->logSystemFactory->create();
        $log->init($type, $class, $message, $details, $additionalData);

        $this->resource->save($log);
    }

    public function isExistErrors(): bool
    {
        $collection = $this->collectionFactory->create();

        $collection
            ->addFieldToFilter(LogSystemResource::COLUMN_TYPE, ['gt' => \M2E\Kaufland\Model\Log\System::TYPE_LOGGER])
            ->setPageSize(1);

        $item = $collection->getFirstItem();

        return !$item->isObjectNew();
    }

    public function getCountExceptionAfterDate(\DateTime $date): int
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('type', ['neq' => '\\' . \M2E\Kaufland\Model\Exception\Connection::class]);
        $collection->addFieldToFilter('type', ['nlike' => '%Logging%']);
        $collection->addFieldToFilter('create_date', ['gt' => $date->format('Y-m-d H:i:s')]);

        return (int)$collection->getSize();
    }

    public function clearByAmount(int $moreThan): void
    {
        $tableName = $this->resource->getMainTable();

        $connection = $this->resource->getConnection();

        $counts = (int)$connection
            ->select()
            ->from($tableName, [new \Zend_Db_Expr('COUNT(*)')])
            ->query()
            ->fetchColumn();

        if ($counts <= $moreThan) {
            return;
        }

        $ids = $connection
            ->select()
            ->from($tableName, 'id')
            ->limit($counts - $moreThan)
            ->order(['id ASC'])
            ->query()
            ->fetchAll(\Zend_Db::FETCH_COLUMN);

        $connection
            ->delete($tableName, 'id IN (' . implode(',', $ids) . ')');
    }

    public function clearByTime(\DateTime $borderDate): void
    {
        $minDate = $borderDate->format('Y-m-d 00:00:00');

        $this->resource->getConnection()->delete($this->resource->getMainTable(), "create_date < '$minDate'");
    }
}
