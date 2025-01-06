<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product;

class RuleFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Rule
    {
        return $this->objectManager->create(Rule::class);
    }
}
