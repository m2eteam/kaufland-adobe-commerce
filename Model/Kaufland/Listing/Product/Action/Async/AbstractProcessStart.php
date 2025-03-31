<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Async;

use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Async;
use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type;
use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\ActionLoggerTrait;

abstract class AbstractProcessStart
{
    use ActionLoggerTrait;

    private \M2E\Kaufland\Model\Product\LockManager $lockManager;
    private \M2E\Kaufland\Model\Product $listingProduct;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator $actionConfigurator;
    private \M2E\Kaufland\Model\Processing\Runner $processingRunner;
    private Async\Processing\InitiatorFactory $processingInitiatorFactory;
    private array $params;
    private int $statusChanger;

    public function initialize(
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Logger $actionLogger,
        \M2E\Kaufland\Model\Product\LockManager $lockManager,
        \M2E\Kaufland\Model\Product $listingProduct,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator $actionConfigurator,
        \M2E\Kaufland\Model\Processing\Runner $processingRunner,
        Async\Processing\InitiatorFactory $processingInitiatorFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\LogBuffer $logBuffer,
        array $params,
        int $statusChanger
    ): void {
        $this->actionLogger = $actionLogger;
        $this->lockManager = $lockManager;
        $this->listingProduct = $listingProduct;
        $this->actionConfigurator = $actionConfigurator;
        $this->processingRunner = $processingRunner;
        $this->processingInitiatorFactory = $processingInitiatorFactory;
        $this->logBuffer = $logBuffer;
        $this->params = $params;
        $this->statusChanger = $statusChanger;
    }

    /**
     * @return \M2E\Core\Helper\Data::STATUS_SUCCESS | \M2E\Core\Helper\Data::STATUS_ERROR
     */
    public function process(): int
    {
        if ($this->lockManager->isLockedByType($this->listingProduct, $this->getProductLockType())) {
            $this->actionLogger->logListingProductMessage(
                $this->listingProduct,
                \M2E\Core\Model\Response\Message::createError(
                    'Another Action is being processed. Try again when the Action is completed.',
                ),
            );

            return \M2E\Core\Helper\Data::STATUS_ERROR;
        }

        $this->prepareProduct();

        $this->lockManager->lock($this->listingProduct, $this->getProductLockType(), $this->getActionNick());

        if (!$this->validateListingProduct()) {
            $this->flushActionLogs();
            $this->lockManager->unlockByType($this->listingProduct, $this->getProductLockType());

            return \M2E\Core\Helper\Data::STATUS_ERROR;
        }

        try {
            $command = $this->getCommand();
            $processParams = $this->getProcessingParams();
            $initiator = $this->processingInitiatorFactory->create($command, $processParams);

            $this->processingRunner->run($initiator);
        } catch (\Throwable $e) {
            $this->actionLogger->logListingProductMessage(
                $this->listingProduct,
                \M2E\Core\Model\Response\Message::createError($e->getMessage())
            );
            $this->lockManager->unlockByType($this->listingProduct, $this->getProductLockType());

            return \M2E\Core\Helper\Data::STATUS_ERROR;
        }

        return \M2E\Core\Helper\Data::STATUS_SUCCESS;
    }

    private function validateListingProduct(): bool
    {
        $validationResult = $this->getActionValidator()->validate();

        foreach ($this->getActionValidator()->getMessages() as $messageData) {
            $this->addActionLogMessage(
                \M2E\Core\Model\Response\Message::create(
                    (string)$messageData['text'],
                    $messageData['type']
                ),
            );
        }

        return $validationResult;
    }

    abstract protected function getActionValidator(): Type\AbstractValidator;

    /**
     * Some data parts can be disallowed from configurator on validateListingProduct() action
     * @return bool
     */
    private function validateConfigurator(): bool
    {
        if (empty($this->actionConfigurator->getAllowedDataTypes())) {
            $this->addActionErrorLog(
                'There was no need for this action. It was skipped.
                Please check the log message above for more detailed information.',
            );

            return false;
        }

        return true;
    }

    abstract protected function getCommand(): \M2E\Core\Model\Connector\CommandProcessingInterface;

    private function getProcessingParams(): \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Async\Processing\Params
    {
        $actionLogger = $this->getActionLogger();

        return new \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Async\Processing\Params(
            $this->getListingProduct()->getId(),
            $actionLogger->getActionId(),
            $actionLogger->getAction(),
            $actionLogger->getInitiator(),
            $this->getActionNick(),
            $this->getParams(),
            $this->getRequestMetadata(),
            $this->getRequestData(),
            $this->getActionConfigurator()->getSerializedData(),
            $this->logBuffer->getWarningMessages(),
            $this->getStatusChanger()
        );
    }

    abstract protected function getActionNick(): string;
    abstract protected function getProductLockType(): string;

    protected function getRequestMetadata(): array
    {
        return [];
    }

    protected function prepareProduct(): void
    {
    }

    protected function getParams(): array
    {
        return $this->params;
    }

    abstract protected function getRequestData();

    protected function getListingProduct(): \M2E\Kaufland\Model\Product
    {
        return $this->listingProduct;
    }

    protected function getAccount(): \M2E\Kaufland\Model\Account
    {
        return $this->listingProduct->getAccount();
    }

    protected function getActionConfigurator(): \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator
    {
        return $this->actionConfigurator;
    }

    public function getResultStatus(): int
    {
        return $this->actionLogger->getStatus();
    }

    public function setStatusChanger(int $statusChanger): void
    {
        $this->statusChanger = $statusChanger;
    }

    protected function getStatusChanger(): int
    {
        return $this->statusChanger;
    }
}
