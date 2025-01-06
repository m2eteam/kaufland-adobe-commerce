<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual;

abstract class AbstractManual
{
    private int $logsActionId;
    private \M2E\Kaufland\Model\Product\ActionCalculator $calculator;
    private \M2E\Kaufland\Model\Listing\LogService $listingLogService;
    private \M2E\Kaufland\Model\Product\LockRepository $lockRepository;

    public function __construct(
        \M2E\Kaufland\Model\Product\ActionCalculator $calculator,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \M2E\Kaufland\Model\Product\LockRepository $lockRepository
    ) {
        $this->calculator = $calculator;
        $this->listingLogService = $listingLogService;
        $this->lockRepository = $lockRepository;
    }

    // ----------------------------------------

    /**
     * @param \M2E\Kaufland\Model\Product[] $listingsProducts
     * @param array $params
     * @param int $logsActionId
     *
     * @return Result
     */
    public function process(array $listingsProducts, array $params, int $logsActionId): Result
    {
        $this->logsActionId = $logsActionId;

        $productIds = [];
        foreach ($listingsProducts as $product) {
            $productIds[] = $product->getId();
        }

        $collectLock = $this->lockRepository->findAllLockProducts($productIds);
        $listingsProducts = $this->prepareOrFilterProducts($listingsProducts, $collectLock);

        if (empty($listingsProducts)) {
            return Result::createSuccess($this->getLogActionId());
        }

        $actions = $this->calculateActions($listingsProducts, $collectLock);
        if (empty($actions)) {
            return Result::createSuccess($this->getLogActionId());
        }

        return $this->processAction($actions, $params);
    }

    abstract protected function getAction(): int;

    /**
     * @param \M2E\Kaufland\Model\Product[] $listingsProducts
     * @param \M2E\Kaufland\Model\Product\LockCollection $lockCollection
     *
     * @return array
     */
    protected function prepareOrFilterProducts(
        array $listingsProducts,
        \M2E\Kaufland\Model\Product\LockCollection $lockCollection
    ): array {
        return $listingsProducts;
    }

    /**
     * @param \M2E\Kaufland\Model\Product[] $products
     * @param \M2E\Kaufland\Model\Product\LockCollection $lockCollection
     *
     * @return \M2E\Kaufland\Model\Product\Action[]
     */
    private function calculateActions(array $products, \M2E\Kaufland\Model\Product\LockCollection $lockCollection): array
    {
        $result = [];
        foreach ($products as $product) {
            $calculateActions = $this->calculateAction($product, $this->calculator, $lockCollection);

            foreach ($calculateActions as $action) {
                if ($action->isActionNothing()) {
                    continue;
                }

                $result[] = $action;
            }

            if (empty($result)) {
                $this->logAboutSkipAction($product, $this->listingLogService);
            }
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
    abstract protected function calculateAction(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Product\ActionCalculator $calculator,
        \M2E\Kaufland\Model\Product\LockCollection $lockCollection
    ): array;

    abstract protected function logAboutSkipAction(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Listing\LogService $logService
    ): void;

    /**
     * @param \M2E\Kaufland\Model\Product\Action[] $actions
     * @param array $params
     *
     * @return Result
     */
    abstract protected function processAction(array $actions, array $params): Result;

    // ----------------------------------------

    protected function getLogActionId(): int
    {
        return $this->logsActionId;
    }

    protected function logForProduct(
        \M2E\Kaufland\Model\Listing\Log\Record $logRecord,
        \M2E\Kaufland\Model\Product $product,
        int $action
    ): void {
        $this->listingLogService
            ->addRecordToProduct(
                $logRecord,
                $product,
                \M2E\Core\Helper\Data::INITIATOR_USER,
                $action,
                $this->getLogActionId(),
            );
    }
}
