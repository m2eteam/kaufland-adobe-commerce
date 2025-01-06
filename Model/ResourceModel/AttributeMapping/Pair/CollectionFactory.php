<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\AttributeMapping\Pair;

class CollectionFactory
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): \M2E\Kaufland\Model\ResourceModel\AttributeMapping\Pair\Collection
    {
        return $this->objectManager->create(\M2E\Kaufland\Model\ResourceModel\AttributeMapping\Pair\Collection::class);
    }
}
