<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\ListUnit;

use M2E\Kaufland\Model\Product\Action\Type\AbstractValidator;

class Processor extends \M2E\Kaufland\Model\Product\Action\AbstractProcessor
{
    private \M2E\Kaufland\Model\Connector\Client\Single $serverClient;
    private \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\Kaufland\Model\TagFactory $tagFactory;
    private ValidatorFactory $actionValidatorFactory;
    private \M2E\Kaufland\Model\Product\Action\Type\AbstractValidator $actionValidator;
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;
    private \M2E\Kaufland\Model\Product\Action\RequestData $requestData;
    private array $requestMetadata;
    private \Magento\Framework\Locale\CurrencyInterface $localeCurrency;
    private \M2E\Kaufland\Model\Product\Repository $productRepository;

    public function __construct(
        ValidatorFactory $actionValidatorFactory,
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory,
        \M2E\Kaufland\Model\Connector\Client\Single $serverClient,
        \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Kaufland\Model\TagFactory $tagFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        \M2E\Kaufland\Model\Product\Action\TagManager $tagManager
    ) {
        parent::__construct($tagManager);

        $this->serverClient = $serverClient;
        $this->tagBuffer = $tagBuffer;
        $this->tagFactory = $tagFactory;
        $this->actionValidatorFactory = $actionValidatorFactory;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->localeCurrency = $localeCurrency;
        $this->productRepository = $productRepository;
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
            $this->getParams()
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

        $command = new \M2E\Kaufland\Model\Channel\Connector\Product\ListCommand(
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

        $responseObj->processSuccess($response->getResponseData());

        $currencyCode = $this->getListingProduct()->getListing()->getStorefront()->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);

        return sprintf(
            'Product was Listed with QTY %d, Price %s',
            $this->getListingProduct()->getOnlineQty(),
            $currency->toCurrency($this->getListingProduct()->getOnlineCurrentPrice()),
        );
    }

    protected function processFail(
        \M2E\Core\Model\Connector\Response\MessageCollection $responseMessageCollection
    ): void {
        $this->addTags($responseMessageCollection->getMessages());
    }

    protected function getActionNick(): string
    {
        return \M2E\Kaufland\Model\Product\Action\DefinitionsCollection::ACTION_UNIT_LIST;
    }

    protected function getProductLockType(): string
    {
        return \M2E\Kaufland\Model\Product\Lock::TYPE_UNIT;
    }

    protected function prepareProduct(): void
    {
        $product =  $this->getListingProduct();
        $offerId = $product->getKauflandOfferId();

        if (empty($offerId)) {
            $skuGenerator = $product->getSkuGenerator();
            $product->setKauflandOfferId($skuGenerator->retrieveSku());
            $this->productRepository->save($product);
        }
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
            $this->tagBuffer->addTags($this->getListingProduct(), $tags);
            $this->tagBuffer->flush();
        }
    }
}
