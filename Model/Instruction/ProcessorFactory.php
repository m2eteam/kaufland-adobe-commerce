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

    public function create(): Processor
    {
        return $this->objectManager->create(Processor::class);
    }
}
