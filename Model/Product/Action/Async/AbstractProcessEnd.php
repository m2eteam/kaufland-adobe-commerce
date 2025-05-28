<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Async;

use M2E\Kaufland\Model\Product\Action\ActionLoggerTrait;

abstract class AbstractProcessEnd
{
    use ActionLoggerTrait;

    private \M2E\Kaufland\Model\Product\LockManager $lockManager;
    private \M2E\Kaufland\Model\Product $listingProduct;
    private array $params;
    private array $requestMetadata;
    private \M2E\Kaufland\Model\Product\Action\RequestData $requestData;
    private int $statusChanger;

    public function initialize(
        \M2E\Kaufland\Model\Product\Action\Logger $actionLogger,
        \M2E\Kaufland\Model\Product\LockManager $lockManager,
        \M2E\Kaufland\Model\Product $listingProduct,
        \M2E\Kaufland\Model\Product\Action\LogBuffer $logBuffer,
        \M2E\Kaufland\Model\Product\Action\RequestData $requestData,
        array $params,
        array $requestMetadata,
        array $warningMessages,
        int $statusChanger
    ): void {
        $this->actionLogger = $actionLogger;
        $this->lockManager = $lockManager;
        $this->listingProduct = $listingProduct;
        $this->logBuffer = $logBuffer;
        $this->params = $params;
        $this->requestMetadata = $requestMetadata;
        $this->requestData = $requestData;
        $this->statusChanger = $statusChanger;

        foreach ($warningMessages as $warningMessage) {
            $this->getLogBuffer()->addWarning($warningMessage);
        }
    }

    public function process(array $resultData, array $messages): void
    {
        try {
            $this->processComplete($resultData, $messages);
        } finally {
            $this->flushActionLogs();
            $this->lockManager->unlockByType($this->listingProduct, $this->getProductLockType());
        }
    }

    abstract protected function processComplete(array $resultData, array $messages): void;

    protected function getListingProduct(): \M2E\Kaufland\Model\Product
    {
        return $this->listingProduct;
    }

    protected function getParams(): array
    {
        return $this->params;
    }

    protected function getRequestMetadata(): array
    {
        return $this->requestMetadata;
    }

    protected function getRequestData(): \M2E\Kaufland\Model\Product\Action\RequestData
    {
        return $this->requestData;
    }

    protected function getStatusChanger(): int
    {
        return $this->statusChanger;
    }

    abstract protected function getProductLockType(): string;

    /**
     * @param \M2E\Core\Model\Connector\Response\Message[] $messages
     *
     * @return void
     */
    protected function addActionLogMessages(array $messages): void
    {
        foreach ($messages as $message) {
            $this->addActionLogMessage($message);
        }
    }
}
