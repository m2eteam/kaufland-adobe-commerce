<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Magento\Product\Rule\Condition;

class CombineFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Combine
    {
        return $this->objectManager->create(Combine::class);
    }
}
