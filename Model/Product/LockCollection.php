<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

class LockCollection
{
    /** @var \M2E\Kaufland\Model\Product\Lock[][] */
    private array $locks;

    public function addLock(\M2E\Kaufland\Model\Product\Lock $lock): void
    {
        $this->locks[$lock->getProductId()][] = $lock;
    }

    public function isLockByProductId(int $productId): bool
    {
        return isset($this->locks[$productId]);
    }

    public function isLockAsProduct(int $productId): bool
    {
        /** @var \M2E\Kaufland\Model\Product\Lock $lockItem */
        foreach ($this->locks[$productId] ?? [] as $lockItem) {
            if ($lockItem->isLockAsProduct()) {
                return true;
            }
        }

        return false;
    }

    public function isLockAsUnit($productId): bool
    {
        /** @var \M2E\Kaufland\Model\Product\Lock $lockItem */
        foreach ($this->locks[$productId] ?? [] as $lockItem) {
            if ($lockItem->isLockAsUnit()) {
                return true;
            }
        }

        return false;
    }
}
