<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product\Rule\Custom;

class CustomFilterFactory
{
    private array $customFiltersMap = [
        Magento\Qty::NICK => Magento\Qty::class,
        Magento\Stock::NICK => Magento\Stock::class,
        Magento\TypeId::NICK => Magento\TypeId::class,
        Kaufland\OnlineCategory::NICK => Kaufland\OnlineCategory::class,
        Kaufland\OnlineTitle::NICK => Kaufland\OnlineTitle::class,
        Kaufland\OnlineQty::NICK => Kaufland\OnlineQty::class,
        Kaufland\OnlineSku::NICK => Kaufland\OnlineSku::class,
        Kaufland\OnlinePrice::NICK => Kaufland\OnlinePrice::class,
        Kaufland\UnitId::NICK => Kaufland\UnitId::class,
        Kaufland\ProductId::NICK => Kaufland\ProductId::class,
        Kaufland\Status::NICK => Kaufland\Status::class,
    ];

    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createByType(string $type): \M2E\Kaufland\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
    {
        $filterClass = $this->choiceCustomFilterClass($type);
        if ($filterClass === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic((string)__('Unknown custom filter - %1', $type));
        }

        return $this->objectManager->create($filterClass);
    }

    private function choiceCustomFilterClass(string $type): ?string
    {
        return $this->customFiltersMap[$type] ?? null;
    }
}
