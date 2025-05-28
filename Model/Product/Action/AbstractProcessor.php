<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action;

abstract class AbstractProcessor
{
    use ActionLoggerTrait;

    private \M2E\Kaufland\Model\Product\Action\TagManager $tagManager;
    private Logger $actionLogger;
    private \M2E\Kaufland\Model\Product\LockManager $lockManager;
    private \M2E\Kaufland\Model\Product $listingProduct;
    private \M2E\Kaufland\Model\Account $account;
    private \M2E\Kaufland\Model\Product\Action\Configurator $actionConfigurator;
    private int $statusChanger;
    private array $params = [];

    public function __construct(
        \M2E\Kaufland\Model\Product\Action\TagManager $tagManager
    ) {
        $this->tagManager = $tagManager;
    }

    // ----------------------------------------

    abstract protected function getActionValidator(): \M2E\Kaufland\Model\Product\Action\Type\AbstractValidator;

    abstract protected function getActionNick(): string;

    abstract protected function getProductLockType(): string;

    abstract protected function makeCall(): \M2E\Core\Model\Connector\Response;

    /**
     * @param \M2E\Core\Model\Connector\Response $response
     *
     * @return string - successful message
     */
    abstract protected function processSuccess(\M2E\Core\Model\Connector\Response $response): string;

    abstract protected function processFail(
        \M2E\Core\Model\Connector\Response\MessageCollection $responseMessageCollection
    ): void;

    // ----------------------------------------

    public function getResultStatus(): int
    {
        return $this->actionLogger->getStatus();
    }

    public function process(): void
    {
        $this->init();

        $this->actionLogger->setStatus(\M2E\Core\Helper\Data::STATUS_SUCCESS);

        if ($this->isListingProductLockedByType($this->listingProduct, $this->getProductLockType())) {
            $this->actionLogger->logListingProductMessage(
                $this->listingProduct,
                \M2E\Core\Model\Response\Message::createError(
                    'Another Action is being processed. Try again when the Action is completed.',
                ),
            );

            return;
        }

        $this->prepareProduct();

        $this->lockManager->lock($this->listingProduct, $this->getProductLockType(), $this->getActionNick());

        try {
            if (!$this->validateListingProduct()) {
                return;
            }

            $apiResponse = $this->makeCall();
            foreach ($apiResponse->getMessageCollection()->getMessages() as $message) {
                $this->addActionLogMessage($message);
            }

            $this->lockManager->unlockByType($this->listingProduct, $this->getProductLockType());

            if ($apiResponse->isResultError()) {
                $this->processFail($apiResponse->getMessageCollection());
            } else {
                $successfulMessage = $this->processSuccess($apiResponse);
                if (!empty($successfulMessage)) {
                    $this->addActionLogMessage(
                        \M2E\Core\Model\Response\Message::createSuccess($successfulMessage)
                    );
                }
            }
        } finally {
            $this->flushActionLogs();
            $this->lockManager->unlockByType($this->listingProduct, $this->getProductLockType());
        }
    }

    private function init(): void
    {
        $this->storedActionLogMessages = [];
        if (
            !isset(
                $this->actionLogger,
                $this->lockManager,
                $this->listingProduct,
                $this->account,
                $this->actionConfigurator,
            )
        ) {
            throw new \LogicException('Processor was not initialized.');
        }
    }

    private function isListingProductLockedByType(\M2E\Kaufland\Model\Product $product, string $productLockType): bool
    {
        return $this->lockManager->isLockedByType($product, $productLockType);
    }

    private function validateListingProduct(): bool
    {
        $validationResult = $this->getActionValidator()->validate();

        foreach ($this->getActionValidator()->getMessages() as $validatorMessage) {
            $this->addActionLogMessage(
                \M2E\Core\Model\Response\Message::create($validatorMessage->getText(), $validatorMessage->getType()),
            );
        }

        if ($validationResult) {
            return true;
        }

        $this->tagManager->addErrorTags($this->listingProduct, $this->getActionValidator()->getMessages());

        return false;
    }

    # region init
    // ----------------------------------------

    public function setStatusChanger(int $statusChanger): void
    {
        $this->statusChanger = $statusChanger;
    }

    protected function getStatusChanger(): int
    {
        return $this->statusChanger;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    protected function getParams(): array
    {
        return $this->params;
    }

    public function setLogBuffer(\M2E\Kaufland\Model\Product\Action\LogBuffer $logBuffer): void
    {
        $this->logBuffer = $logBuffer;
    }

    public function setActionLogger(\M2E\Kaufland\Model\Product\Action\Logger $logger): void
    {
        $this->actionLogger = $logger;
    }

    public function setLockManager(\M2E\Kaufland\Model\Product\LockManager $lockManager): void
    {
        $this->lockManager = $lockManager;
    }

    public function setListingProduct(\M2E\Kaufland\Model\Product $listingProduct): void
    {
        $this->listingProduct = $listingProduct;
        $this->account = $this->listingProduct->getAccount();
    }

    protected function getListingProduct(): \M2E\Kaufland\Model\Product
    {
        return $this->listingProduct;
    }

    protected function getAccount(): \M2E\Kaufland\Model\Account
    {
        return $this->account;
    }

    public function setActionConfigurator(
        \M2E\Kaufland\Model\Product\Action\Configurator $configurator
    ): void {
        $this->actionConfigurator = $configurator;
    }

    protected function getActionConfigurator(): \M2E\Kaufland\Model\Product\Action\Configurator
    {
        return $this->actionConfigurator;
    }

    # endregion

    protected function prepareProduct(): void
    {
    }

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
