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

    public function __construct(
        ValidatorFactory $actionValidatorFactory,
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory,
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

        $this->processSuccessRevisePrice();
        $this->processSuccessReviseQty();
        $this->processSuccessReviseShipping();

        if ($this->getActionConfigurator()->isExcludingMode()) {
            return 'Item was Revised';
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

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Magento\Framework\Currency\Exception\CurrencyException
     */
    private function processSuccessRevisePrice(): void
    {
        if (!$this->getActionConfigurator()->isPriceAllowed()) {
            return;
        }

        $from = $this->getListingProduct()
                     ->getOrigData(\M2E\Kaufland\Model\ResourceModel\Product::COLUMN_ONLINE_PRICE);
        $to = $this->getListingProduct()->getOnlineCurrentPrice();
        if ($from == $to) {
            return;
        }

        $storefront = $this->getListingProduct()->getListing()->getStorefront();

        $currencyCode = $storefront->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);

        $message = sprintf(
            'Price was revised from %s to %s',
            $currency->toCurrency($from),
            $currency->toCurrency($to)
        );

        $this->addActionLogMessage(\M2E\Core\Model\Response\Message::createSuccess($message));
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function processSuccessReviseQty(): void
    {
        if (!$this->getActionConfigurator()->isQtyAllowed()) {
            return;
        }

        $from = $this->getListingProduct()
                     ->getOrigData(\M2E\Kaufland\Model\ResourceModel\Product::COLUMN_ONLINE_QTY);
        $to = $this->getListingProduct()->getOnlineQty();

        if ($from == $to) {
            return;
        }

        $message = sprintf('QTY was revised from %s to %s', $from, $to);
        $this->addActionLogMessage(\M2E\Core\Model\Response\Message::createSuccess($message));
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function processSuccessReviseShipping(): void
    {
        if (!$this->getActionConfigurator()->isShippingAllowed()) {
            return;
        }

        $fromHandlingTime = (int)$this->getListingProduct()
                                      ->getOrigData(
                                          \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_ONLINE_HANDLING_TIME
                                      );
        $toHandlingTime = $this->getListingProduct()->getOnlineHandlingTime();

        if ($fromHandlingTime !== $toHandlingTime) {
            $message = sprintf('Handling Time was revised from %s to %s', $fromHandlingTime, $toHandlingTime);
            $this->addActionLogMessage(\M2E\Core\Model\Response\Message::createSuccess($message));
        }
    }
}
