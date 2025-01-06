<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action;

class ProcessorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private LoggerFactory $loggerFactory;
    private \M2E\Kaufland\Model\Product\LockManagerFactory $lockManagerFactory;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\LogBuffer $logBuffer;

    public function __construct(
        LoggerFactory $loggerFactory,
        \M2E\Kaufland\Model\Product\LockManagerFactory $lockManagerFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\LogBuffer $logBuffer
    ) {
        $this->objectManager = $objectManager;
        $this->loggerFactory = $loggerFactory;
        $this->lockManagerFactory = $lockManagerFactory;
        $this->logBuffer = $logBuffer;
    }

    private function create(
        string $processorClass,
        \M2E\Kaufland\Model\Product $listingProduct,
        Configurator $configurator,
        Logger $actionLogger,
        array $params
    ): AbstractProcessor {
        /** @var AbstractProcessor $obj */
        $obj = $this->objectManager->create($processorClass);

        $obj->setListingProduct($listingProduct);
        $obj->setActionConfigurator($configurator);

        $obj->setActionLogger($actionLogger);

        $obj->setLockManager(
            $this->createLockManager(
                $listingProduct,
                $actionLogger,
            ),
        );

        $obj->setParams($params);
        $obj->setLogBuffer($this->logBuffer);

        return $obj;
    }

    public function createListProcessor(
        \M2E\Kaufland\Model\Product $listingProduct,
        Configurator $configurator,
        int $statusChanger,
        int $actionLogId,
        array $params
    ): Type\ListUnit\Processor {
        $actionLogger = $this->createActionLogger(
            $statusChanger,
            $actionLogId,
            \M2E\Kaufland\Model\Listing\Log::ACTION_LIST_PRODUCT,
        );

        /** @var \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ListUnit\Processor */
        return $this->create(
            Type\ListUnit\Processor::class,
            $listingProduct,
            $configurator,
            $actionLogger,
            $params,
        );
    }

    public function createReviseProcessor(
        \M2E\Kaufland\Model\Product $listingProduct,
        Configurator $configurator,
        int $statusChanger,
        int $actionLogId,
        array $params
    ): Type\ReviseUnit\Processor {
        $actionLogger = $this->createActionLogger(
            $statusChanger,
            $actionLogId,
            \M2E\Kaufland\Model\Listing\Log::ACTION_REVISE_PRODUCT,
        );

        /** @var \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseUnit\Processor */
        return $this->create(
            Type\ReviseUnit\Processor::class,
            $listingProduct,
            $configurator,
            $actionLogger,
            $params,
        );
    }

    public function createRelistProcessor(
        \M2E\Kaufland\Model\Product $listingProduct,
        Configurator $configurator,
        int $statusChanger,
        int $actionLogId,
        array $params
    ): Type\Relist\Processor {
        $actionLogger = $this->createActionLogger(
            $statusChanger,
            $actionLogId,
            \M2E\Kaufland\Model\Listing\Log::ACTION_RELIST_PRODUCT,
        );

        /** @var Type\Relist\Processor */
        return $this->create(
            Type\Relist\Processor::class,
            $listingProduct,
            $configurator,
            $actionLogger,
            $params,
        );
    }

    public function createDeleteProcessor(
        \M2E\Kaufland\Model\Product $listingProduct,
        Configurator $configurator,
        int $statusChanger,
        int $actionLogId,
        array $params
    ): Type\Delete\Processor {
        $actionLogger = $this->createActionLogger(
            $statusChanger,
            $actionLogId,
            \M2E\Kaufland\Model\Listing\Log::ACTION_REMOVE_PRODUCT,
        );

        /** @var Type\Delete\Processor */
        return $this->create(
            Type\Delete\Processor::class,
            $listingProduct,
            $configurator,
            $actionLogger,
            $params,
        );
    }

    public function createStopProcessor(
        \M2E\Kaufland\Model\Product $listingProduct,
        Configurator $configurator,
        int $statusChanger,
        int $actionLogId,
        array $params
    ): Type\Stop\Processor {
        $actionLogger = $this->createActionLogger(
            $statusChanger,
            $actionLogId,
            \M2E\Kaufland\Model\Listing\Log::ACTION_STOP_PRODUCT,
        );

        /** @var Type\Stop\Processor */
        return $this->create(
            Type\Stop\Processor::class,
            $listingProduct,
            $configurator,
            $actionLogger,
            $params,
        );
    }

    // ----------------------------------------

    private function createActionLogger(
        int $statusChanger,
        int $logActionId,
        int $logAction
    ): Logger {
        return $this->loggerFactory->create(
            $logActionId,
            $logAction,
            $this->getInitiatorByChanger($statusChanger),
        );
    }

    private function createLockManager(
        \M2E\Kaufland\Model\Product $listingProduct,
        Logger $logger
    ): \M2E\Kaufland\Model\Product\LockManager {
        return $this->lockManagerFactory->create(
            $listingProduct,
            $logger->getInitiator(),
            $logger->getActionId(),
            $logger->getAction(),
        );
    }

    private function getInitiatorByChanger(int $statusChanger): int
    {
        switch ($statusChanger) {
            case \M2E\Kaufland\Model\Product::STATUS_CHANGER_UNKNOWN:
                return \M2E\Core\Helper\Data::INITIATOR_UNKNOWN;
            case \M2E\Kaufland\Model\Product::STATUS_CHANGER_USER:
                return \M2E\Core\Helper\Data::INITIATOR_USER;
            default:
                return \M2E\Core\Helper\Data::INITIATOR_EXTENSION;
        }
    }
}
