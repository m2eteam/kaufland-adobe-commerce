<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Schedule;

class StopAndRemoveAction extends AbstractSchedule
{
    private \M2E\Kaufland\Model\Product\RemoveHandler $removeHandler;

    public function __construct(
        \M2E\Kaufland\Model\Product\RemoveHandler $removeHandler,
        \M2E\Kaufland\Model\ScheduledAction\CreateService $scheduledActionCreateService,
        \M2E\Kaufland\Model\Product\ActionCalculator $calculator,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \M2E\Kaufland\Model\Product\LockRepository $lockRepository
    ) {
        parent::__construct($scheduledActionCreateService, $calculator, $listingLogService, $lockRepository);
        $this->removeHandler = $removeHandler;
    }

    protected function getAction(): int
    {
        return \M2E\Kaufland\Model\Product::ACTION_DELETE_UNIT;
    }

    protected function prepareOrFilterProducts(
        array $listingsProducts,
        \M2E\Kaufland\Model\Product\LockCollection $lockCollection
    ): array {
        $result = [];
        foreach ($listingsProducts as $listingProduct) {
            if ($lockCollection->isLockByProductId($listingProduct->getId())) {
                continue;
            }

            if ($listingProduct->isRetirable()) {
                $result[] = $listingProduct;

                continue;
            }

            $this->removeHandler->process($listingProduct);
        }

        return $result;
    }

    /**
     * @param \M2E\Kaufland\Model\Product $product
     * @param \M2E\Kaufland\Model\Product\ActionCalculator $calculator
     * @param \M2E\Kaufland\Model\Product\LockCollection $lockCollection
     *
     * @return \M2E\Kaufland\Model\Product\Action[]
     */
    protected function calculateAction(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Product\ActionCalculator $calculator,
        \M2E\Kaufland\Model\Product\LockCollection $lockCollection
    ): array {
        return [\M2E\Kaufland\Model\Product\Action::createDelete($product)];
    }

    protected function logAboutSkipAction(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Listing\LogService $logService
    ): void {
    }
}
