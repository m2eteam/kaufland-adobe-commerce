<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order\Log;

class ServiceFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Service
    {
        return $this->objectManager->create(Service::class);
    }
}
