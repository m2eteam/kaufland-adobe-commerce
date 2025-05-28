<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Manual\Realtime;

use M2E\Kaufland\Model\Product\Action\Manual\Result;

class ListAction extends AbstractRealtime
{
    use \M2E\Kaufland\Model\Product\Action\Manual\SkipMessageTrait;

    private \M2E\Kaufland\Model\Product\Action\Dispatcher $actionDispatcher;
    private \M2E\Kaufland\Model\Product\Action\Async\DispatcherAsync $actionDispatcherAsync;
    public function __construct(
        \M2E\Kaufland\Model\Product\Action\Async\DispatcherAsync $actionDispatcherAsync,
        \M2E\Kaufland\Model\Product\Action\Dispatcher $actionDispatcher,
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
        if ($lockCollection->isLockByProductId($product->getId())) {
            return [];
        }

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

    protected function processAction(array $actions, array $params): Result
    {
        $params['logs_action_id'] = $this->getLogActionId();

        $packageCollection = new \M2E\Kaufland\Model\Product\Action\PackageCollection();
        foreach ($actions as $action) {
            $packageCollection->add($action->getProduct(), $action->getConfigurator());
        }

        $result = null;

        /** @var \M2E\Kaufland\Model\Product\Action\Package $package */
        foreach ($packageCollection->getAll() as $package) {
            $product = $package->getProduct();
            if ($product->isListableAsProduct()) {
                $result = $this->actionDispatcherAsync->processList(
                    $product,
                    $params,
                    \M2E\Kaufland\Model\Product::STATUS_CHANGER_USER
                );
                $packageCollection->remove($product->getId());
            }
        }

        if (!$packageCollection->isEmpty()) {
            $result = $this->actionDispatcher->process(
                $this->getAction(),
                $packageCollection,
                $params,
                \M2E\Kaufland\Model\Product::STATUS_CHANGER_USER,
            );
        }

        if ($result === \M2E\Core\Helper\Data::STATUS_ERROR) {
            return Result::createError($this->getLogActionId());
        }

        if ($result === \M2E\Core\Helper\Data::STATUS_WARNING) {
            return Result::createWarning($this->getLogActionId());
        }

        return Result::createSuccess($this->getLogActionId());
    }
}
