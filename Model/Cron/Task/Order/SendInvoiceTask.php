<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task\Order;

class SendInvoiceTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/sendInvoice';

    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\Order\Change\SendInvoiceProcessor $sendInvoiceProcessor;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Order\Change\SendInvoiceProcessor $sendInvoiceProcessor
    ) {
        $this->accountRepository = $accountRepository;
        $this->sendInvoiceProcessor = $sendInvoiceProcessor;
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
            $this->sendInvoiceProcessor->process($account);
        }
    }
}
