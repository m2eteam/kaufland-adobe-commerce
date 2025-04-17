<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Schedule;

class ReviseAction extends AbstractSchedule
{
    use \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\SkipMessageTrait;

    protected function getAction(): int
    {
        return \M2E\Kaufland\Model\Product::ACTION_REVISE_UNIT;
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
        $result = [];

        if (
            !$lockCollection->isLockAsUnit($product->getId())
            && $product->isRevisableAsUnit()
        ) {
            $action = $calculator->calculateToReviseOrStopUnit($product);
            if ($action->isActionStop()) {
                return [\M2E\Kaufland\Model\Product\Action::createNothing($product)];
            }

            $result[$action->getAction()] = $action;
        }

        if ($product->isRevisable()) {
            $result[] = $calculator->calculateToReviseProduct($product, true, true, true, true);
        }

        if (
            !$lockCollection->isLockAsProduct($product->getId())
            && $product->isRevisableAsProduct()
        ) {
            if (!$product->isReadyForReviseAsProduct()) {
                $this->logForProduct(
                    \M2E\Kaufland\Model\Listing\Log\Record::createWarning(
                        (string)__(
                            'Product details (Title, Description, Images, Category) could not be updated. To revise the data, please ensure that
        a Description Policy is assigned to the Listing and a proper %channel_title category is set for the Product.',
                            [
                                'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                            ]
                        )
                    ),
                    $product,
                    \M2E\Kaufland\Model\Listing\Log::ACTION_REVISE_PRODUCT,
                );
            } else {
                $action = $calculator->calculateToReviseProduct(
                    $product,
                    true,
                    true,
                    true,
                    true,
                );

                $result[$action->getAction()] = $action;
            }
        }

        if (empty($result)) {
            $result[] = \M2E\Kaufland\Model\Product\Action::createNothing($product);
        }

        return $result;
    }

    protected function logAboutSkipAction(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Listing\LogService $logService
    ): void {
        $logService->addProduct(
            $product,
            \M2E\Core\Helper\Data::INITIATOR_USER,
            \M2E\Kaufland\Model\Listing\Log::ACTION_REVISE_PRODUCT,
            $this->getLogActionId(),
            $this->createSkipReviseMessage(),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
