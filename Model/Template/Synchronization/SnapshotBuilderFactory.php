<?php

namespace M2E\Kaufland\Model\Template\Synchronization;

class SnapshotBuilderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): SnapshotBuilder
    {
        return $this->objectManager->create(SnapshotBuilder::class);
    }
}
