<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Wizard\SearchChannelProductManager;

class FindResult
{
    private bool $isCompleted;
    private int $totalProductCount;
    private int $processedProductCount;

    public function __construct(
        bool $isCompleted,
        int $totalProductCount,
        int $processedProductCount
    ) {
        $this->isCompleted = $isCompleted;
        $this->totalProductCount = $totalProductCount;
        $this->processedProductCount = $processedProductCount;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function getTotalProductCount(): int
    {
        return $this->totalProductCount;
    }

    public function getProcessedProductCount(): int
    {
        return $this->processedProductCount;
    }
}
