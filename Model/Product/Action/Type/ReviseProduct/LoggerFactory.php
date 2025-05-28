<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\ReviseProduct;

class LoggerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Logger
    {
        return $this->objectManager->create(Logger::class);
    }
}
