<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action;

class LoggerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        int $actionId,
        int $action,
        int $initiator
    ): Logger {
        return $this->objectManager->create(
            Logger::class,
            ['actionId' => $actionId, 'action' => $action, 'initiator' => $initiator],
        );
    }
}
