<?php

namespace M2E\Kaufland\Model\Template\Synchronization;

class DiffFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Diff
    {
        return $this->objectManager->create(Diff::class);
    }
}
