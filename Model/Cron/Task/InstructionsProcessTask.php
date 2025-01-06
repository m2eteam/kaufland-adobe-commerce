<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task;

class InstructionsProcessTask extends \M2E\Kaufland\Model\Cron\AbstractTask
{
    public const NICK = 'instructions/process';

    private \M2E\Kaufland\Model\Instruction\ProcessorFactory $instructionProcessorFactory;

    public function __construct(
        \M2E\Kaufland\Model\Cron\Manager $cronManager,
        \M2E\Kaufland\Model\Synchronization\LogService $syncLogger,
        \M2E\Core\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource,
        \M2E\Kaufland\Model\Instruction\ProcessorFactory $instructionProcessorFactory
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

        $this->instructionProcessorFactory = $instructionProcessorFactory;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $processor = $this->instructionProcessorFactory->create();
        $processor->process();
    }
}
