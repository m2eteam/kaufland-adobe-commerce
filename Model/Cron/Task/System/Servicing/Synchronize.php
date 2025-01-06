<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task\System\Servicing;

class Synchronize extends \M2E\Kaufland\Model\Cron\AbstractTask
{
    public const NICK = 'system/servicing/synchronize';

    private \M2E\Kaufland\Model\Servicing\Dispatcher $dispatcher;

    public function __construct(
        \M2E\Kaufland\Model\Servicing\Dispatcher $dispatcher,
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
        $this->dispatcher = $dispatcher;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $this->dispatcher->process();
    }
}
