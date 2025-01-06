<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task\Order;

class SyncTask extends \M2E\Kaufland\Model\Cron\AbstractTask
{
    public const NICK = 'order/sync';

    /** @var int in seconds */
    protected int $intervalInSeconds = 300;

    private Sync\OrdersProcessorFactory $ordersProcessorFactory;
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    private \M2E\Kaufland\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Helper\Module\Exception $exceptionHelper,
        \M2E\Kaufland\Model\Cron\Manager $cronManager,
        Sync\OrdersProcessorFactory $ordersProcessorFactory,
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
        $this->ordersProcessorFactory = $ordersProcessorFactory;
        $this->accountRepository = $accountRepository;
        $this->exceptionHelper = $exceptionHelper;
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
            try {
                $ordersProcessor = $this->ordersProcessorFactory->create($account, $synchronizationLog);
                $ordersProcessor->process();
            } catch (\Throwable $e) {
                $this->exceptionHelper->process($e);
                $synchronizationLog->addFromException($e);
            }
        }
    }
}
