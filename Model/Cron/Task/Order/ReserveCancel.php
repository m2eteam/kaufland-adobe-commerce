<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task\Order;

class ReserveCancel extends \M2E\Kaufland\Model\Cron\AbstractTask
{
    public const NICK = 'order/reserve_cancel';

    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\Order\ReserveCancelProcessor $reserveCancelProcessor;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Order\ReserveCancelProcessor $reserveCancelProcessor,
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
            $resource
        );
        $this->accountRepository = $accountRepository;
        $this->reserveCancelProcessor = $reserveCancelProcessor;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function getSynchronizationLog(): \M2E\Kaufland\Model\Synchronization\LogService
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setTask(\M2E\Kaufland\Model\Synchronization\Log::TASK_ORDERS);

        return $synchronizationLog;
    }

    protected function performActions(): void
    {
        $permittedAccounts = $this->accountRepository->getAll();

        if (empty($permittedAccounts)) {
            return;
        }

        $this->getSynchronizationLog()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_EXTENSION);

        foreach ($permittedAccounts as $account) {
            $this->getOperationHistory()->addText('Starting Account "' . $account->getTitle() . '"');

            try {
                $this->reserveCancelProcessor->process($account);
            } catch (\Exception $exception) {
                $message = (string)__(
                    'The "Reserve Cancellation" Action for Account "%1" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }
}
