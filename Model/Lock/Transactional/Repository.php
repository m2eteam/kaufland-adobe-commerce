<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Lock\Transactional;

use M2E\Kaufland\Model\ResourceModel\Lock\Transactional as TransactionalResource;

class Repository
{
    private \M2E\Kaufland\Model\Lock\TransactionalFactory $transactionalFactory;
    private TransactionalResource $resource;

    public function __construct(
        \M2E\Kaufland\Model\Lock\TransactionalFactory $transactionalFactory,
        TransactionalResource $resource
    ) {
        $this->transactionalFactory = $transactionalFactory;
        $this->resource = $resource;
    }

    public function create(\M2E\Kaufland\Model\Lock\Transactional $transactional): void
    {
        $this->resource->save($transactional);
    }

    public function findByNick(string $nick): ?\M2E\Kaufland\Model\Lock\Transactional
    {
        $entity = $this->transactionalFactory->createEmpty();
        $this->resource->load($entity, $nick, TransactionalResource::COLUMN_NICK);

        if ($entity->isObjectNew()) {
            return null;
        }

        return $entity;
    }

    public function findExclusiveLockIdByNick(string $nick): ?int
    {
        $connection = $this->resource->getConnection();

        $result = (int)$connection->select()
                                  ->from($this->resource->getMainTable(), [TransactionalResource::COLUMN_ID])
                                  ->where('nick = ?', $nick)
                                  ->forUpdate()
                                  ->query()
                                  ->fetchColumn();

        return $result === 0 ? null : $result;
    }

    public function lockTable(): void
    {
        $connection = $this->resource->getConnection();
        $connection->query("LOCK TABLES `{$this->resource->getMainTable()}` WRITE");
    }

    public function unlockTable(): void
    {
        $connection = $this->resource->getConnection();
        $connection->query('UNLOCK TABLES');
    }

    public function startTransaction(): void
    {
        $connection = $this->resource->getConnection();
        $connection->beginTransaction();
    }

    public function commitTransaction(): void
    {
        $connection = $this->resource->getConnection();
        $connection->commit();
    }
}
