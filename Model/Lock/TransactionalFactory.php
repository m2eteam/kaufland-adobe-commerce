<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Lock;

class TransactionalFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Transactional
    {
        /** @var Transactional */
        return $this->objectManager->create(Transactional::class);
    }

    public function create(string $nick): Transactional
    {
        $obj = $this->createEmpty();
        $obj->create($nick);

        return $obj;
    }
}
