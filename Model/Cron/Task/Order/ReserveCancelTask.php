<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task\Order;

class ReserveCancelTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/reserve_cancel';

    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\Order\ReserveCancelProcessor $reserveCancelProcessor;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Order\ReserveCancelProcessor $reserveCancelProcessor
    ) {
        $this->accountRepository = $accountRepository;
        $this->reserveCancelProcessor = $reserveCancelProcessor;
    }

    /**
     * @param \M2E\Kaufland\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\Kaufland\Model\Synchronization\Log::TASK_ORDERS);
        $context->getSynchronizationLog()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_EXTENSION);

        $permittedAccounts = $this->accountRepository->getAll();

        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $account) {
            $context->getOperationHistory()->addText('Starting Account "' . $account->getTitle() . '"');

            try {
                $this->reserveCancelProcessor->process($account);
            } catch (\Throwable $exception) {
                $message = (string)__(
                    'The "Reserve Cancellation" Action for Account "%1" was completed with error.',
                    $account->getTitle()
                );

                $context->getExceptionHandler()->processTaskAccountException($message, __FILE__, __LINE__);
                $context->getExceptionHandler()->processTaskException($exception);
            }
        }
    }
}
