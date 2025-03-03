<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product\Attribute;

use M2E\Kaufland\Model\Magento\Product\Attribute\RetrieveValue;

class RetrieveValueFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        string $attributeTitle,
        \M2E\Kaufland\Model\Magento\Product $magentoProduct
    ): RetrieveValue {
        return $this->objectManager->create(
            RetrieveValue::class,
            ['attributeTitle' => $attributeTitle, 'magentoProduct' => $magentoProduct]
        );
    }
}
