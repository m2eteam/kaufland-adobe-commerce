<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task\Order;

class CancelTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/cancel';

    private \M2E\Kaufland\Model\Order\Change\CancelProcessor $cancelProcessor;
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Kaufland\Model\Order\Change\CancelProcessor $cancelProcessor,
        \M2E\Kaufland\Model\Account\Repository $accountRepository
    ) {
        $this->cancelProcessor = $cancelProcessor;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param \M2E\Kaufland\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $synchronizationLog = $context->getSynchronizationLog();
        $synchronizationLog->setTask(\M2E\Kaufland\Model\Synchronization\Log::TASK_ORDERS);

        foreach ($this->accountRepository->getAll() as $account) {
            $this->cancelProcessor->process($account);
        }
    }
}
