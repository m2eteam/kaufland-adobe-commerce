<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action;

class ProcessorAsyncFactory
{
    private LoggerFactory $loggerFactory;
    private \M2E\Kaufland\Model\Product\Action\Async\Factory $asyncActionFactory;
    private \M2E\Kaufland\Model\Product\LockManager $lockManager;
    private \M2E\Kaufland\Model\Processing\Runner $processingRunner;
    private \M2E\Kaufland\Model\Product\Action\Async\Processing\InitiatorFactory $initiatorFactory;
    private \M2E\Kaufland\Model\Product\Action\LogBufferFactory $logBufferFactory;
    private \M2E\Kaufland\Model\Product\LockManagerFactory $lockManagerFactory;

    public function __construct(
        LoggerFactory                                                        $loggerFactory,
        \M2E\Kaufland\Model\Product\Action\Async\Factory                     $asyncActionFactory,
        \M2E\Kaufland\Model\Processing\Runner                                $processingRunner,
        \M2E\Kaufland\Model\Product\Action\Async\Processing\InitiatorFactory $initiatorFactory,
        \M2E\Kaufland\Model\Product\Action\LogBufferFactory                  $logBufferFactory,
        \M2E\Kaufland\Model\Product\LockManagerFactory                       $lockManagerFactory
    ) {
        $this->loggerFactory = $loggerFactory;
        $this->asyncActionFactory = $asyncActionFactory;
        $this->processingRunner = $processingRunner;
        $this->initiatorFactory = $initiatorFactory;
        $this->logBufferFactory = $logBufferFactory;
        $this->lockManagerFactory = $lockManagerFactory;
    }

    public function createProcessStart(
        string $nick,
        \M2E\Kaufland\Model\Product $listingProduct,
        Configurator $configurator,
        int $statusChanger,
        int $actionLogId,
        int $logAction,
        array $params
    ): \M2E\Kaufland\Model\Product\Action\Async\AbstractProcessStart {
        $actionLogger = $this->loggerFactory->create(
            $actionLogId,
            $logAction,
            $this->getInitiatorByChanger($statusChanger),
        );

        $action = $this->asyncActionFactory->createActionStart($nick);
        $lockManager = $this->createLockManager(
            $listingProduct,
            $actionLogger,
        );
        $action->initialize(
            $actionLogger,
            $lockManager,
            $listingProduct,
            $configurator,
            $this->processingRunner,
            $this->initiatorFactory,
            $this->logBufferFactory->create(),
            $params,
            $statusChanger
        );

        return $action;
    }

    public function createProcessEnd(
        string $nick,
        \M2E\Kaufland\Model\Product $listingProduct,
        int $initiator,
        int $actionLogId,
        int $actionLog,
        array $params,
        array $requestMetadata,
        array $requestData,
        array $warningMessages,
        int $statusChanger
    ): \M2E\Kaufland\Model\Product\Action\Async\AbstractProcessEnd {
        $actionLogger = $this->loggerFactory->create(
            $actionLogId,
            $actionLog,
            $initiator
        );

        $action = $this->asyncActionFactory->createActionEnd($nick);
        $lockManager = $this->createLockManager(
            $listingProduct,
            $actionLogger,
        );
        $action->initialize(
            $actionLogger,
            $lockManager,
            $listingProduct,
            $this->logBufferFactory->create(),
            $this->createRequestData($requestData),
            $params,
            $requestMetadata,
            $warningMessages,
            $statusChanger
        );

        return $action;
    }

    private function createRequestData(array $data): \M2E\Kaufland\Model\Product\Action\RequestData
    {
        $requestData = new \M2E\Kaufland\Model\Product\Action\RequestData();
        $requestData->setData($data);

        return $requestData;
    }

    // ----------------------------------------

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
}
