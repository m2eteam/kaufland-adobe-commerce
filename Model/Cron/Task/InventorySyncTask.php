<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task;

use M2E\Kaufland\Model\Storefront;

class InventorySyncTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
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
        \M2E\Kaufland\Model\Listing\InventorySync\Processing\InitiatorFactory $processingInitiatorFactory
    ) {
        $this->accountRepository = $accountRepository;
        $this->processingRunner = $processingRunner;
        $this->lockRepository = $lockRepository;
        $this->processingInitiatorFactory = $processingInitiatorFactory;
    }

    /**
     * @param \M2E\Kaufland\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\Kaufland\Model\Synchronization\Log::TASK_OTHER_LISTINGS);
        $context->getSynchronizationLog()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_EXTENSION);

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

                $context->getOperationHistory()->addText(
                    "Starting Account (Storefront) '{$account->getTitle()} ({$storefront->getStorefrontCode()})'",
                );
                $context->getOperationHistory()->addTimePoint(
                    $timePointId = __METHOD__ . 'process' . $account->getId() . $storefront->getStorefrontCode(),
                    "Process Account '{$account->getTitle()} ({$storefront->getStorefrontCode()})'",
                );

                // ----------------------------------------

                try {
                    $initiator = $this->processingInitiatorFactory->create($account, $storefront);
                    $this->processingRunner->run($initiator);
                } catch (\Throwable $e) {
                    $context->getOperationHistory()->addText(
                        "Error '{$account->getTitle()} ({$storefront->getStorefrontCode()})'. Message: {$e->getMessage()}",
                    );
                }

                // ----------------------------------------

                $context->getOperationHistory()->saveTimePoint($timePointId);
            }
        }
    }
}
