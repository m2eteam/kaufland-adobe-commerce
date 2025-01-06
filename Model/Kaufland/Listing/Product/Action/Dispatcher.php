<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action;

class Dispatcher
{
    private ?int $logsActionId = null;
    private \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\Kaufland\Model\TagFactory $tagFactory;
    private \M2E\Kaufland\Helper\Module\Exception $exceptionHelper;
    private ProcessorFactory $processorFactory;
    /** @var \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\ConfiguratorFactory */
    private ConfiguratorFactory $configuratorFactory;
    private \M2E\Kaufland\Model\Listing\LogService $listingLogService;

    public function __construct(
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        ProcessorFactory $processorFactory,
        \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Kaufland\Model\TagFactory $tagFactory,
        \M2E\Kaufland\Helper\Module\Exception $exceptionHelper,
        ConfiguratorFactory $configuratorFactory
    ) {
        $this->tagBuffer = $tagBuffer;
        $this->tagFactory = $tagFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->processorFactory = $processorFactory;
        $this->configuratorFactory = $configuratorFactory;
        $this->listingLogService = $listingLogService;
    }

    public function process(int $action, PackageCollection $packageCollection, array $params, int $statusChanger): int
    {
        if (empty($params['logs_action_id'])) {
            $this->logsActionId = $this->listingLogService->getNextActionId();
            $params['logs_action_id'] = $this->logsActionId;
        } else {
            $this->logsActionId = $params['logs_action_id'];
        }

        $groupedPackages = $this->groupProductsByAccountAndStorefront($packageCollection);

        $params += ['status_changer' => $statusChanger];

        return $this->processAccountsStorefront(
            $groupedPackages,
            $params,
            $statusChanger,
            $action
        );
    }

    // ----------------------------------------

    /**
     * @param \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\PackageCollection $packageCollection
     *
     * @return Package[][][]
     */
    private function groupProductsByAccountAndStorefront(
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\PackageCollection $packageCollection
    ): array {
        $packages = [];
        foreach ($packageCollection->getAll() as $package) {
            $product = $package->getProduct();

            $accountId = $product->getListing()->getAccountId();
            $storefrontId = $product->getListing()->getStorefrontId();

            $packages[$accountId][$storefrontId][] = $package;
        }

        return $packages;
    }

    /**
     * @param Package[][][] $groupedPackages
     * @param array $params
     * @param int $statusChanger
     * @param int $action
     *
     * @return int
     */
    private function processAccountsStorefront(
        array $groupedPackages,
        array $params,
        int $statusChanger,
        int $action
    ): int {
        $results = [];
        foreach ($groupedPackages as $accountPackages) {
            foreach ($accountPackages as $storefrontPackages) {
                try {
                    $result = $this->processProducts(
                        $storefrontPackages,
                        $action,
                        $params,
                        $statusChanger
                    );
                } catch (\Throwable $exception) {
                    foreach ($storefrontPackages as $package) {
                        $this->logListingProductException($package->getProduct(), $exception, $action, $statusChanger);
                    }

                    $this->exceptionHelper->process($exception);

                    $result = \M2E\Core\Helper\Data::STATUS_ERROR;
                }

                $results[] = $result;
            }
        }

        $this->tagBuffer->flush();

        return \M2E\Core\Helper\Data::getMainStatus($results);
    }

    /**
     * @param Package[] $packages
     * @param int $action
     * @param array $params
     * @param int $statusChanger
     *
     * @return int
     */
    private function processProducts(
        array $packages,
        int $action,
        array $params,
        int $statusChanger
    ): int {
        $results = [];

        foreach ($packages as $package) {
            $this->tagBuffer->removeAllTags($package->getProduct());
        }

        $this->tagBuffer->flush();

        foreach ($packages as $package) {
            try {
                $processor = $this->createProcessor(
                    $package,
                    $action,
                    $params,
                    $statusChanger,
                    $this->logsActionId
                );

                $processor->process();

                $result = $processor->getResultStatus();

                if ($result === \M2E\Core\Helper\Data::STATUS_ERROR) {
                    $this->tagBuffer->addTag($package->getProduct(), $this->tagFactory->createWithHasErrorCode());
                }
            } catch (\Throwable $exception) {
                $this->logListingProductException($package->getProduct(), $exception, $action, $statusChanger);
                $this->exceptionHelper->process($exception);

                $result = \M2E\Core\Helper\Data::STATUS_ERROR;
            }

            $results[] = $result;
        }

        return \M2E\Core\Helper\Data::getMainStatus($results);
    }

    private function createProcessor(
        Package $package,
        int $action,
        array $params,
        int $statusChanger,
        int $logsActionId
    ): AbstractProcessor {
        switch ($action) {
            case \M2E\Kaufland\Model\Product::ACTION_LIST_UNIT:
                return $this->processorFactory->createListProcessor(
                    $package->getProduct(),
                    $package->getActionConfigurator(),
                    $statusChanger,
                    $logsActionId,
                    $params,
                );

            case \M2E\Kaufland\Model\Product::ACTION_RELIST_UNIT:
                return $this->processorFactory->createRelistProcessor(
                    $package->getProduct(),
                    $package->getActionConfigurator(),
                    $statusChanger,
                    $logsActionId,
                    $params,
                );

            case \M2E\Kaufland\Model\Product::ACTION_REVISE_UNIT:
                return $this->processorFactory->createReviseProcessor(
                    $package->getProduct(),
                    $package->getActionConfigurator(),
                    $statusChanger,
                    $logsActionId,
                    $params,
                );

            case \M2E\Kaufland\Model\Product::ACTION_STOP_UNIT:
                return $this->processorFactory->createStopProcessor(
                    $package->getProduct(),
                    $package->getActionConfigurator(),
                    $statusChanger,
                    $logsActionId,
                    $params
                );

            case \M2E\Kaufland\Model\Product::ACTION_DELETE_UNIT:
                return $this->processorFactory->createDeleteProcessor(
                    $package->getProduct(),
                    $package->getActionConfigurator(),
                    $statusChanger,
                    $logsActionId,
                    $params,
                );

            default:
                throw new \DomainException("Unknown action '$action'");
        }
    }

    // ----------------------------------------

    private function logListingProductException(
        \M2E\Kaufland\Model\Product $listingProduct,
        \Throwable $exception,
        int $action,
        int $statusChanger
    ): void {
        $action = $this->recognizeActionForLogging($action);
        $initiator = $this->recognizeInitiatorForLogging($statusChanger);

        $this->listingLogService->addProduct(
            $listingProduct,
            $initiator,
            $action,
            $this->logsActionId,
            $exception->getMessage(),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_ERROR,
        );
    }

    private function recognizeInitiatorForLogging(int $statusChanger): int
    {
        if ($statusChanger === \M2E\Kaufland\Model\Product::STATUS_CHANGER_UNKNOWN) {
            return \M2E\Core\Helper\Data::INITIATOR_UNKNOWN;
        }
        if ($statusChanger === \M2E\Kaufland\Model\Product::STATUS_CHANGER_USER) {
            return \M2E\Core\Helper\Data::INITIATOR_USER;
        }

        return \M2E\Core\Helper\Data::INITIATOR_EXTENSION;
    }

    private function recognizeActionForLogging(int $action): int
    {
        $logAction = \M2E\Kaufland\Model\Listing\Log::ACTION_UNKNOWN;

        switch ($action) {
            case \M2E\Kaufland\Model\Product::ACTION_LIST_UNIT:
                $logAction = \M2E\Kaufland\Model\Listing\Log::ACTION_LIST_PRODUCT;
                break;
            case \M2E\Kaufland\Model\Product::ACTION_RELIST_UNIT:
                $logAction = \M2E\Kaufland\Model\Listing\Log::ACTION_RELIST_PRODUCT;
                break;
            case \M2E\Kaufland\Model\Product::ACTION_REVISE_UNIT:
                $logAction = \M2E\Kaufland\Model\Listing\Log::ACTION_REVISE_PRODUCT;
                break;
            case \M2E\Kaufland\Model\Product::ACTION_STOP_UNIT:
                $logAction = \M2E\Kaufland\Model\Listing\Log::ACTION_STOP_PRODUCT;
                break;
            case \M2E\Kaufland\Model\Product::ACTION_DELETE_UNIT:
                $logAction = \M2E\Kaufland\Model\Listing\Log::ACTION_REMOVE_PRODUCT;
                break;
        }

        return $logAction;
    }
}
