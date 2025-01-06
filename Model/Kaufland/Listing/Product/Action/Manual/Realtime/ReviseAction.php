<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Realtime;

use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Result;

class ReviseAction extends AbstractRealtime
{
    use \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\SkipMessageTrait;

    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Dispatcher $actionDispatcher;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Async\DispatcherAsync $actionDispatcherAsync;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Async\DispatcherAsync $actionDispatcherAsync,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Dispatcher $actionDispatcher,
        \M2E\Kaufland\Model\Product\ActionCalculator $calculator,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \M2E\Kaufland\Model\Product\LockRepository $lockRepository
    ) {
        parent::__construct($calculator, $listingLogService, $lockRepository);
        $this->actionDispatcherAsync = $actionDispatcherAsync;
        $this->actionDispatcher = $actionDispatcher;
    }

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

        if (
            !$lockCollection->isLockAsProduct($product->getId())
            && $product->isRevisable()
        ) {
            if (!$product->isReadyForReviseAsProduct()) {
                $this->logForProduct(
                    \M2E\Kaufland\Model\Listing\Log\Record::createWarning(
                        (string)__(
                            'Product details (Title, Description, Images, Category) could not be updated. To revise the data, please ensure that
             a Description Policy is assigned to the Listing and a proper Kaufland category is set for the Product.'
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

        if ($product->isIncomplete() && !isset($result[\M2E\Kaufland\Model\Product::ACTION_REVISE_PRODUCT])) {
            $result = [];
        }

        if (empty($result)) {
            $result[] = \M2E\Kaufland\Model\Product\Action::createNothing($product);
        }

        return array_values($result);
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

    protected function processAction(array $actions, array $params): Result
    {
        $params['logs_action_id'] = $this->getLogActionId();

        $packageCollection = new \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\PackageCollection();
        foreach ($actions as $action) {
            if ($action->isActionReviseProduct()) {
                $this->actionDispatcherAsync->processRevise(
                    $action->getProduct(),
                    $params,
                    \M2E\Kaufland\Model\Product::STATUS_CHANGER_USER
                );

                continue;
            }

            $packageCollection->add($action->getProduct(), $action->getConfigurator());
        }

        $result = $this->actionDispatcher->process(
            $this->getAction(),
            $packageCollection,
            $params,
            \M2E\Kaufland\Model\Product::STATUS_CHANGER_USER,
        );

        if ($result === \M2E\Core\Helper\Data::STATUS_ERROR) {
            return Result::createError($this->getLogActionId());
        }

        if ($result === \M2E\Core\Helper\Data::STATUS_WARNING) {
            return Result::createWarning($this->getLogActionId());
        }

        return Result::createSuccess($this->getLogActionId());
    }
}
