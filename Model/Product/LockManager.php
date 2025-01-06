<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

class LockManager
{
    private const LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME = 3600; // 1 hour

    private \M2E\Kaufland\Model\Product $listingProduct;
    private int $initiator;
    private int $logsActionId;
    private int $logsAction;

    private \M2E\Kaufland\Model\Listing\LogService $listingLogService;
    /** @var \M2E\Kaufland\Model\Product\LockFactory */
    private LockFactory $lockFactory;
    /** @var \M2E\Kaufland\Model\Product\LockRepository */
    private LockRepository $lockRepository;

    public function __construct(
        \M2E\Kaufland\Model\Product $listingProduct,
        int $initiator,
        int $logsActionId,
        int $logsAction,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \M2E\Kaufland\Model\Product\LockFactory $lockFactory,
        \M2E\Kaufland\Model\Product\LockRepository $lockRepository
    ) {
        $this->listingProduct = $listingProduct;
        $this->initiator = $initiator;
        $this->logsActionId = $logsActionId;
        $this->logsAction = $logsAction;
        $this->listingLogService = $listingLogService;
        $this->lockFactory = $lockFactory;
        $this->lockRepository = $lockRepository;
    }

    // ----------------------------------------

    public function isLocked(\M2E\Kaufland\Model\Product $product): bool
    {
        $lock = $this->lockRepository->findByProductId($product->getId());
        if ($lock === null) {
            return false;
        }

        if ($this->isInactiveMoreThanSeconds($lock, self::LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME)) {
            $this->unlock($product);

            return false;
        }

        return true;
    }

    public function isLockedByType(\M2E\Kaufland\Model\Product $product, string $productLockType): bool
    {
        $lock = $this->lockRepository->findByProductIdAndType(
            $product->getId(),
            $productLockType
        );

        if ($lock === null) {
            return false;
        }

        if ($this->isInactiveMoreThanSeconds($lock, self::LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME)) {
            $this->unlockByType($product, $productLockType);

            return false;
        }

        return true;
    }

    public function isInactiveMoreThanSeconds(Lock $lockItem, $maxInactiveInterval): bool
    {
        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();
        $createDate = $lockItem->getCreateDate();

        return $createDate->getTimestamp() < ($currentDate->getTimestamp() - $maxInactiveInterval);
    }

    public function checkLocking(\M2E\Kaufland\Model\Product $product): bool
    {
        if (!$this->isLocked($product)) {
            return false;
        }

        $this->listingLogService->addProduct(
            $this->listingProduct,
            $this->initiator,
            $this->logsAction,
            $this->logsActionId,
            (string)__('Another Action is being processed. Try again when the Action is completed.'),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_ERROR,
        );

        return true;
    }

    // ----------------------------------------

    public function lock(\M2E\Kaufland\Model\Product $product, string $productLockType, string $initiator): void
    {
        if ($this->isLockedByType($product, $initiator)) {
            throw new \LogicException('Current product has already been locked.');
        }

        $lock = $this->lockFactory->create(
            $product->getId(),
            $productLockType,
            $initiator,
            \M2E\Core\Helper\Date::createCurrentGmt()
        );
        $this->lockRepository->create($lock);
    }

    public function unlock(\M2E\Kaufland\Model\Product $product): void
    {
        $lock = $this->lockRepository->findByProductId($product->getId());
        if ($lock === null) {
            return;
        }

        $this->lockRepository->remove($lock);
    }

    public function unlockByType(\M2E\Kaufland\Model\Product $product, string $productLockType): void
    {
        $lock = $this->lockRepository->findByProductIdAndType(
            $product->getId(),
            $productLockType
        );
        if ($lock === null) {
            return;
        }

        $this->lockRepository->remove($lock);
    }
}
