<?php

namespace M2E\Kaufland\Model\ResourceModel\Collection;

class CustomFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(array $data = []): Custom
    {
        return $this->objectManager->create(Custom::class, $data);
    }
}
