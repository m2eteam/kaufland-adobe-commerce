<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category;

class AttributeFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): \M2E\Kaufland\Model\Category\Attribute
    {
        return $this->objectManager->create(\M2E\Kaufland\Model\Category\Attribute::class);
    }
}
