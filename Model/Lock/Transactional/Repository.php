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

    public function retrieveLock(string $nick): ?int
    {
        $lockId = (int)$this->resource->getConnection()
                                      ->select()
                                      ->from($this->getTableName(), ['id'])
                                      ->where('nick = ?', $nick)
                                      ->forUpdate()
                                      ->query()
                                      ->fetchColumn();

        if (empty($lockId)) {
            return null;
        }

        return $lockId;
    }

    public function lockTable(): void
    {
        $connection = $this->resource->getConnection();
        $connection->query("LOCK TABLES `{$this->getTableName()}` WRITE");
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

    // ----------------------------------------

    private function getTableName(): string
    {
        return $this->resource->getMainTable();
    }
}
