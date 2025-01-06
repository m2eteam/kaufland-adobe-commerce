<?php

namespace M2E\Kaufland\Model\Kaufland\Order\Item;

class BuilderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(array $data = []): Builder
    {
        return $this->objectManager->create(Builder::class, $data);
    }
}
