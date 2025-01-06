<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category;

class TreeFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Tree
    {
        return $this->objectManager->create(Tree::class);
    }
}