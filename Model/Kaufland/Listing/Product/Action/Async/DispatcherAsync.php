<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Async;

use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\DefinitionsCollection as AsyncActions;
use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator;

class DispatcherAsync
{
    private \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\Kaufland\Model\TagFactory $tagFactory;
    private \M2E\Kaufland\Helper\Module\Exception $exceptionHelper;
    private \M2E\Kaufland\Model\Listing\LogService $listingLogService;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\ProcessorAsyncFactory $processorAsyncFactory;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\ConfiguratorFactory $configuratorFactory;

    public function __construct(
        \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Kaufland\Model\TagFactory $tagFactory,
        \M2E\Kaufland\Helper\Module\Exception $exceptionHelper,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\ProcessorAsyncFactory $processorAsyncFactory,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\ConfiguratorFactory $configuratorFactory
    ) {
        $this->tagBuffer = $tagBuffer;
        $this->tagFactory = $tagFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->listingLogService = $listingLogService;
        $this->processorAsyncFactory = $processorAsyncFactory;
        $this->configuratorFactory = $configuratorFactory;
    }

    /**
     * @param \M2E\Kaufland\Model\Product $product
     * @param array $params
     * @param int $statusChanger
     *
     * @return \M2E\Core\Helper\Data::STATUS_SUCCESS | \M2E\Core\Helper\Data::STATUS_ERROR
     */
    public function processList(\M2E\Kaufland\Model\Product $product, array $params, int $statusChanger): int
    {
        $logsActionId = $this->getLogActionId($params);
        $params += ['logs_action_id' => $logsActionId];

        $this->removeTags($product);

        try {
            $processor = $this->processorAsyncFactory->createProcessStart(
                AsyncActions::ACTION_PRODUCT_LIST,
                $product,
                $this->getActionConfigurator($product),
                $statusChanger,
                $logsActionId,
                \M2E\Kaufland\Model\Listing\Log::ACTION_LIST_PRODUCT,
                $params,
            );

            $result = $processor->process();
            if ($result === \M2E\Core\Helper\Data::STATUS_ERROR) {
                $this->tagBuffer->addTag($product, $this->tagFactory->createWithHasErrorCode());
                $this->tagBuffer->flush();
            }

            return $result;
        } catch (\Throwable $exception) {
            $this->logListingProductException(
                $product,
                $exception,
                \M2E\Kaufland\Model\Product::ACTION_LIST_PRODUCT,
                $statusChanger,
                $logsActionId
            );
            $this->exceptionHelper->process($exception);

            return \M2E\Core\Helper\Data::STATUS_ERROR;
        }
    }

    /**
     * @param \M2E\Kaufland\Model\Product $product
     * @param array $params
     * @param int $statusChanger
     *
     * @return \M2E\Core\Helper\Data::STATUS_SUCCESS | \M2E\Core\Helper\Data::STATUS_ERROR
     */
    public function processRevise(\M2E\Kaufland\Model\Product $product, array $params, int $statusChanger): int
    {
        $logsActionId = $this->getLogActionId($params);
        $params += ['logs_action_id' => $logsActionId];

        $this->removeTags($product);

        try {
            $processor = $this->processorAsyncFactory->createProcessStart(
                AsyncActions::ACTION_PRODUCT_REVISE,
                $product,
                $this->getActionConfigurator($product),
                $statusChanger,
                $logsActionId,
                \M2E\Kaufland\Model\Listing\Log::ACTION_REVISE_PRODUCT,
                $params,
            );

            $result = $processor->process();
            if ($result === \M2E\Core\Helper\Data::STATUS_ERROR) {
                $this->tagBuffer->addTag($product, $this->tagFactory->createWithHasErrorCode());
                $this->tagBuffer->flush();
            }

            return $result;
        } catch (\Throwable $exception) {
            $this->logListingProductException(
                $product,
                $exception,
                \M2E\Kaufland\Model\Product::ACTION_REVISE_PRODUCT,
                $statusChanger,
                $logsActionId
            );
            $this->exceptionHelper->process($exception);

            return \M2E\Core\Helper\Data::STATUS_ERROR;
        }
    }

    private function getLogActionId(array $params): int
    {
        if (!empty($params['logs_action_id'])) {
            return $params['logs_action_id'];
        }

        return $this->listingLogService->getNextActionId();
    }

    private function getActionConfigurator(\M2E\Kaufland\Model\Product $product): Configurator
    {
        if ($product->getActionConfigurator() === null) {
            $actionConfigurator = $this->configuratorFactory->create();
            $product->setActionConfigurator($actionConfigurator);
        }

        return $product->getActionConfigurator();
    }

    private function removeTags(\M2E\Kaufland\Model\Product $listingProduct): void
    {
        $this->tagBuffer->removeAllTags($listingProduct);
        $this->tagBuffer->flush();
    }

    private function logListingProductException(
        \M2E\Kaufland\Model\Product $listingProduct,
        \Throwable $exception,
        int $action,
        int $statusChanger,
        int $logActionId
    ): void {
        $action = $this->recognizeActionForLogging($action);
        $initiator = $this->recognizeInitiatorForLogging($statusChanger);

        $this->listingLogService->addProduct(
            $listingProduct,
            $initiator,
            $action,
            $logActionId,
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
            case \M2E\Kaufland\Model\Product::ACTION_LIST_PRODUCT:
                $logAction = \M2E\Kaufland\Model\Listing\Log::ACTION_LIST_PRODUCT;
                break;
            case \M2E\Kaufland\Model\Product::ACTION_REVISE_PRODUCT:
                $logAction = \M2E\Kaufland\Model\Listing\Log::ACTION_REVISE_PRODUCT;
                break;
        }

        return $logAction;
    }
}
