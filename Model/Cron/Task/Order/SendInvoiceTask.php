<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task\Order;

use M2E\Kaufland\Model\Order\Change\SendInvoiceProcessor;

class SendInvoiceTask extends \M2E\Kaufland\Model\Cron\AbstractTask
{
    public const NICK = 'order/sendInvoice';

    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\Order\Change\SendInvoiceProcessor $sendInvoiceProcessor;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Order\Change\SendInvoiceProcessor $sendInvoiceProcessor,
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
        $this->sendInvoiceProcessor = $sendInvoiceProcessor;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $synchronizationLog = $this->getSynchronizationLog();
        $synchronizationLog->setTask(\M2E\Kaufland\Model\Synchronization\Log::TASK_ORDERS);

        foreach ($this->accountRepository->getAll() as $account) {
            $this->sendInvoiceProcessor->process($account);
        }
    }
}
