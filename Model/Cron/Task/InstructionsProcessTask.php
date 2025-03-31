<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task;

class InstructionsProcessTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'instructions/process';

    private \M2E\Kaufland\Model\Instruction\ProcessorFactory $instructionProcessorFactory;

    public function __construct(
        \M2E\Kaufland\Model\Instruction\ProcessorFactory $instructionProcessorFactory
    ) {
        $this->instructionProcessorFactory = $instructionProcessorFactory;
    }

    public function process($context): void
    {
        $processor = $this->instructionProcessorFactory->create();
        $processor->process();
    }
}
