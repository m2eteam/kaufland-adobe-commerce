<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task;

class InstructionsProcessTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'instructions/process';

    private \M2E\Kaufland\Model\Instruction\ProcessorFactory $instructionProcessorFactory;
    private \M2E\Kaufland\Model\Instruction\SynchronizationTemplate\Handler $synchronizationTemplate;
    private \M2E\Kaufland\Model\Instruction\AutoAction\Handler $autoActionHandler;

    public function __construct(
        \M2E\Kaufland\Model\Instruction\ProcessorFactory $instructionProcessorFactory,
        \M2E\Kaufland\Model\Instruction\SynchronizationTemplate\Handler $synchronizationTemplate,
        \M2E\Kaufland\Model\Instruction\AutoAction\Handler $autoActionHandler
    ) {
        $this->instructionProcessorFactory = $instructionProcessorFactory;
        $this->synchronizationTemplate = $synchronizationTemplate;
        $this->autoActionHandler = $autoActionHandler;
    }

    public function process($context): void
    {
        $processor = $this->instructionProcessorFactory->create([
            $this->autoActionHandler,
            $this->synchronizationTemplate,
        ]);
        $processor->process();
    }
}
