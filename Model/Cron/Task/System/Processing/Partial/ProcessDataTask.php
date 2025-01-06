<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task\System\Processing\Partial;

class ProcessDataTask extends \M2E\Kaufland\Model\Cron\AbstractTask
{
    public const NICK = 'processing/process/partial/data';

    private \M2E\Kaufland\Model\Processing\ProcessResult\Partial $processResultPartial;
    private \M2E\Kaufland\Model\Processing\Lock\ClearMissed $lockClearMissed;

    public function __construct(
        \M2E\Kaufland\Model\Processing\ProcessResult\Partial $processResultPartial,
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

        $this->processResultPartial = $processResultPartial;
        $this->lockClearMissed = $lockClearMissed;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $this->processResultPartial->processExpired();

        $this->processResultPartial->processData();

        $this->lockClearMissed->process();
    }
}
