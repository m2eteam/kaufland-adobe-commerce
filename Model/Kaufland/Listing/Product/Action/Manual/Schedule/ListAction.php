<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Schedule;

class ListAction extends AbstractSchedule
{
    use \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\SkipMessageTrait;

    protected function getAction(): int
    {
        return \M2E\Kaufland\Model\Product::ACTION_LIST_UNIT;
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
        return [$calculator->calculateToList($product)];
    }

    protected function logAboutSkipAction(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Listing\LogService $logService
    ): void {
        $logService->addProduct(
            $product,
            \M2E\Core\Helper\Data::INITIATOR_USER,
            \M2E\Kaufland\Model\Listing\Log::ACTION_LIST_PRODUCT,
            $this->getLogActionId(),
            $this->createSkipListMessage(),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
