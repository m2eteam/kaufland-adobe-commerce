<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Schedule;

class RelistAction extends AbstractSchedule
{
    use \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\SkipMessageTrait;

    protected function getAction(): int
    {
        return \M2E\Kaufland\Model\Product::ACTION_RELIST_UNIT;
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
        return [$calculator->calculateToRelist($product, \M2E\Kaufland\Model\Product::STATUS_CHANGER_USER)];
    }

    protected function logAboutSkipAction(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Listing\LogService $logService
    ): void {
        $logService->addProduct(
            $product,
            \M2E\Core\Helper\Data::INITIATOR_USER,
            \M2E\Kaufland\Model\Listing\Log::ACTION_RELIST_PRODUCT,
            $this->getLogActionId(),
            $this->createSkipRelistMessage(),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
