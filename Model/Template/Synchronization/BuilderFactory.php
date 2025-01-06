<?php

namespace M2E\Kaufland\Model\Template\Synchronization;

class BuilderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Builder
    {
        return $this->objectManager->create(Builder::class);
    }
}
