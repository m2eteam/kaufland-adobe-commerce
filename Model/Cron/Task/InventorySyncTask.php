<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task;

use M2E\Kaufland\Model\Storefront;

class InventorySyncTask extends \M2E\Kaufland\Model\Cron\AbstractTask
{
    public const NICK = 'inventory/sync';

    private const SYNC_INTERVAL_8_HOURS_IN_SECONDS = 28800;

    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\Processing\Runner $processingRunner;
    private \M2E\Kaufland\Model\Processing\Lock\Repository $lockRepository;
    private \M2E\Kaufland\Model\Listing\InventorySync\Processing\InitiatorFactory $processingInitiatorFactory;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Processing\Runner $processingRunner,
        \M2E\Kaufland\Model\Processing\Lock\Repository $lockRepository,
        \M2E\Kaufland\Model\Listing\InventorySync\Processing\InitiatorFactory $processingInitiatorFactory,
        \M2E\Kaufland\Model\Cron\Manager $cronManager,
        \M2E\Kaufland\Model\Synchronization\LogService $syncLogger,
        \M2E\Core\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $modelFactory,
            $activeRecordFactory,
            $taskRepo,
            $resource,
        );
        $this->accountRepository = $accountRepository;
        $this->processingRunner = $processingRunner;
        $this->lockRepository = $lockRepository;
        $this->processingInitiatorFactory = $processingInitiatorFactory;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function getSynchronizationLog(): \M2E\Kaufland\Model\Synchronization\LogService
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setTask(\M2E\Kaufland\Model\Synchronization\Log::TASK_OTHER_LISTINGS);
        $synchronizationLog->setInitiator(\M2E\Core\Helper\Data::INITIATOR_EXTENSION);

        return $synchronizationLog;
    }

    protected function performActions(): void
    {
        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();
        foreach ($this->accountRepository->findActiveWithEnabledInventorySync() as $account) {
            foreach ($account->getStorefronts() as $storefront) {
                if (
                    $storefront->getInventoryLastSyncDate() !== null
                    && $storefront->getInventoryLastSyncDate()->modify(
                        '+ ' . self::SYNC_INTERVAL_8_HOURS_IN_SECONDS . ' seconds',
                    ) > $currentDate
                ) {
                    continue;
                }

                if ($this->lockRepository->isExist(Storefront::LOCK_NICK, $storefront->getId())) {
                    continue;
                }

                $this->getOperationHistory()->addText(
                    "Starting Account (Storefront) '{$account->getTitle()} ({$storefront->getStorefrontCode()})'",
                );
                $this->getOperationHistory()->addTimePoint(
                    $timePointId = __METHOD__ . 'process' . $account->getId() . $storefront->getStorefrontCode(),
                    "Process Account '{$account->getTitle()} ({$storefront->getStorefrontCode()})'",
                );

                // ----------------------------------------

                try {
                    $initiator = $this->processingInitiatorFactory->create($account, $storefront);
                    $this->processingRunner->run($initiator);
                } catch (\Throwable $e) {
                    $this->getOperationHistory()->addText(
                        "Error '{$account->getTitle()} ({$storefront->getStorefrontCode()})'. Message: {$e->getMessage()}",
                    );
                }

                // ----------------------------------------

                $this->getOperationHistory()->saveTimePoint($timePointId);
            }
        }
    }
}
