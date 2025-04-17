<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseUnit;

use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractValidator;

class Processor extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\AbstractProcessor
{
    private \M2E\Kaufland\Model\Connector\Client\Single $serverClient;
    private ValidatorFactory $actionValidatorFactory;
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;
    private \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\Kaufland\Model\Kaufland\TagFactory $tagFactory;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractValidator $actionValidator;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\RequestData $requestData;
    private \Magento\Framework\Locale\CurrencyInterface $localeCurrency;
    private array $requestMetadata;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseUnit\LoggerFactory $loggerFactory;

    public function __construct(
        ValidatorFactory $actionValidatorFactory,
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseUnit\LoggerFactory $loggerFactory,
        \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Kaufland\Model\Kaufland\TagFactory $tagFactory,
        \M2E\Kaufland\Model\Connector\Client\Single $serverClient,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        $this->serverClient = $serverClient;
        $this->actionValidatorFactory = $actionValidatorFactory;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->tagBuffer = $tagBuffer;
        $this->tagFactory = $tagFactory;
        $this->localeCurrency = $localeCurrency;
        $this->loggerFactory = $loggerFactory;
    }

    protected function getActionValidator(): AbstractValidator
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->actionValidator)) {
            return $this->actionValidator;
        }

        return $this->actionValidator = $this->actionValidatorFactory->create(
            $this->getListingProduct(),
            $this->getActionConfigurator(),
            $this->getParams(),
        );
    }

    protected function makeCall(): \M2E\Core\Model\Connector\Response
    {
        $request = $this->requestFactory->create(
            $this->getListingProduct(),
            $this->getActionConfigurator(),
            $this->getLogBuffer(),
            $this->getParams(),
        );

        $this->requestData = $request->build();
        $this->requestMetadata = $request->getMetaData();

        $command = new \M2E\Kaufland\Model\Kaufland\Connector\Item\ReviseCommand(
            $this->getAccount()->getServerHash(),
            $this->requestData->getData(),
        );

        /** @var \M2E\Core\Model\Connector\Response */
        return $this->serverClient->process($command);
    }

    protected function processSuccess(\M2E\Core\Model\Connector\Response $response): string
    {
        /** @var Response $responseObj */
        $responseObj = $this->responseFactory->create(
            $this->getListingProduct(),
            $this->getActionConfigurator(),
            $this->requestData,
            $this->getParams(),
            $this->getStatusChanger(),
            $this->requestMetadata,
        );

        $logger = $this->loggerFactory->create();
        $logger->saveProductDataBeforeUpdate($this->getListingProduct());

        $responseData = $response->getResponseData();
        if (!$responseObj->isSuccess($responseData)) {
            $messages = $responseObj->getMessages($responseData);

            $this->addTags($messages);
            $this->addActionLogMessages($messages);

            return '';
        }

        $responseObj->processSuccess($responseData);

        $messages = $responseObj->getMessages($responseData);
        if (!empty($messages)) {
            $this->addTags($messages);
            $this->addActionLogMessages($messages);
        }

        $logs = $logger->calculateLogs($this->getListingProduct());

        if (empty($logs)) {
            return 'Item was Revised';
        }

        foreach ($logs as $log) {
            $this->addActionLogMessage($log);
        }

        return '';
    }

    protected function processFail(
        \M2E\Core\Model\Connector\Response\MessageCollection $responseMessageCollection
    ): void {
        $this->addTags($responseMessageCollection->getMessages());
    }

    protected function getActionNick(): string
    {
        return \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\DefinitionsCollection::ACTION_UNIT_REVISE;
    }

    protected function getProductLockType(): string
    {
        return \M2E\Kaufland\Model\Product\Lock::TYPE_UNIT;
    }

    /**
     * @param \M2E\Core\Model\Connector\Response\Message[] $messages
     *
     * @return void
     */
    private function addTags(
        array $messages
    ): void {
        $allowedCodesOfWarnings = [];

        $tags = [];
        foreach ($messages as $message) {
            if (
                !$message->isSenderComponent()
                || empty($message->getCode())
            ) {
                continue;
            }

            if (
                $message->isError()
                || ($message->isWarning() && in_array($message->getCode(), $allowedCodesOfWarnings))
            ) {
                $tags[] = $this->tagFactory->createByErrorCode((string)$message->getCode(), $message->getText());
            }
        }

        if (!empty($tags)) {
            $tags[] = $this->tagFactory->createWithHasErrorCode();

            $this->tagBuffer->addTags($this->getListingProduct(), $tags);
            $this->tagBuffer->flush();
        }
    }
}
