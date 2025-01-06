<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task\System\Processing\Simple;

class ProcessDataTask extends \M2E\Kaufland\Model\Cron\AbstractTask
{
    public const NICK = 'processing/simple/process/data';

    private \M2E\Kaufland\Model\Processing\ProcessResult\Simple $processResultSimple;
    private \M2E\Kaufland\Model\Processing\Lock\ClearMissed $lockClearMissed;

    public function __construct(
        \M2E\Kaufland\Model\Processing\ProcessResult\Simple $processResultSimple,
        \M2E\Kaufland\Model\Processing\Lock\ClearMissed $lockClearMissed,
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

        $this->processResultSimple = $processResultSimple;
        $this->lockClearMissed = $lockClearMissed;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $this->processResultSimple->processExpired();

        $this->processResultSimple->processData();

        $this->lockClearMissed->process();
    }
}
