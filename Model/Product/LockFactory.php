<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

class LockFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(int $productId, string $type, string $initiator, \DateTime $createDate): Lock
    {
        $lock = $this->objectManager->create(Lock::class);

        $lock->init($productId, $type, $initiator, $createDate);

        return $lock;
    }
}
