<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Instruction;

class ProcessorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \M2E\Kaufland\Model\Instruction\Handler\HandlerInterface[] $handlers
     */
    public function create(array $handlers): Processor
    {
        return $this->objectManager->create(Processor::class, [
            'handlers' => $handlers
        ]);
    }
}
