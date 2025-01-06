<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Lock\Transactional;

class Manager
{
    private string $nick;

    private bool $isTableLocked = false;
    private bool $isTransactionStarted = false;

    private Repository $lockTransactionalRepository;
    private \M2E\Kaufland\Model\Lock\TransactionalFactory $lockTransactionalFactory;

    public function __construct(
        string $nick,
        \M2E\Kaufland\Model\Lock\Transactional\Repository $lockTransactionalRepository,
        \M2E\Kaufland\Model\Lock\TransactionalFactory $lockTransactionalFactory
    ) {
        $this->nick = $nick;
        $this->lockTransactionalRepository = $lockTransactionalRepository;
        $this->lockTransactionalFactory = $lockTransactionalFactory;
    }

    public function __destruct()
    {
        $this->unlock();
    }

    // ----------------------------------------

    public function getNick(): string
    {
        return $this->nick;
    }

    public function lock(): void
    {
        if ($this->getExclusiveLock()) {
            return;
        }

        $this->createExclusiveLock();
        $this->getExclusiveLock();
    }

    public function unlock(): void
    {
        if ($this->isTableLocked) {
            $this->unlockTable();
        }

        if ($this->isTransactionStarted) {
            $this->commitTransaction();
        }
    }

    // ----------------------------------------

    private function getExclusiveLock(): bool
    {
        $this->startTransaction();

        $lockId = $this->lockTransactionalRepository->findExclusiveLockIdByNick($this->nick);
        if ($lockId !== null) {
            return true;
        }

        $this->commitTransaction();

        return false;
    }

    private function startTransaction(): void
    {
        $this->lockTransactionalRepository->startTransaction();

        $this->isTransactionStarted = true;
    }

    private function commitTransaction(): void
    {
        $this->lockTransactionalRepository->commitTransaction();

        $this->isTransactionStarted = false;
    }

    private function createExclusiveLock(): void
    {
        $this->lockTable();

        $lock = $this->lockTransactionalRepository->findByNick($this->nick);
        if ($lock === null) {
            $lock = $this->lockTransactionalFactory->create($this->nick);

            $this->lockTransactionalRepository->create($lock);
        }

        $this->unlockTable();
    }

    // ----------------------------------------

    private function lockTable(): void
    {
        $this->lockTransactionalRepository->lockTable();

        $this->isTableLocked = true;
    }

    private function unlockTable(): void
    {
        $this->lockTransactionalRepository->unlockTable();

        $this->isTableLocked = false;
    }
}
