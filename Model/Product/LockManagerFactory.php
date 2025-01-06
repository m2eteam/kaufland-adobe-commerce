<?php

namespace M2E\Kaufland\Model\Product;

class LockManagerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Kaufland\Model\Product $listingProduct,
        int $initiator,
        int $logsActionId,
        int $logsAction
    ): \M2E\Kaufland\Model\Product\LockManager {
        return $this->objectManager->create(
            \M2E\Kaufland\Model\Product\LockManager::class,
            [
                'listingProduct' => $listingProduct,
                'initiator' => $initiator,
                'logsAction' => $logsAction,
                'logsActionId' => $logsActionId,
            ],
        );
    }
}
