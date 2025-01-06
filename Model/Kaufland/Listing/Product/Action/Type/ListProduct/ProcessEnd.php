<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ListProduct;

class ProcessEnd extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Async\AbstractProcessEnd
{
    private \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\Kaufland\Model\Kaufland\TagFactory $tagFactory;
    private ResponseFactory $responseFactory;
    private \Magento\Framework\Locale\CurrencyInterface $localeCurrency;

    public function __construct(
        \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Kaufland\Model\Kaufland\TagFactory $tagFactory,
        ResponseFactory $responseFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        $this->tagBuffer = $tagBuffer;
        $this->tagFactory = $tagFactory;
        $this->responseFactory = $responseFactory;
        $this->localeCurrency = $localeCurrency;
    }

    protected function processComplete(array $resultData, array $messages): void
    {
        if (empty($resultData)) {
            $this->processFail($messages);

            return;
        }

        $this->processSuccess($resultData);
    }

    private function processSuccess(array $data): void
    {
        /** @var Response $responseObj */
        $responseObj = $this->responseFactory->create(
            $this->getListingProduct(),
            $this->getListingProduct()->getActionConfigurator(),
            $this->getRequestData(),
            $this->getParams(),
            $this->getRequestMetadata()
        );

        if (!$responseObj->isSuccess($data)) {
            $messages = $responseObj->getMessages($data);
            $this->addTags($messages);
            $this->addActionLogMessages($messages);

            if ($responseObj->isProductCreated($data)) {
                $responseObj->processSuccessOnlyProduct($data);
            }

            return;
        }

        $responseObj->processSuccess($data);

        $messages = $responseObj->getMessages($data);
        if (!empty($messages)) {
            $this->addTags($messages);
            $this->addActionLogMessages($messages);
        }

        $this->generateResultMessage($data);
    }

    /**
     * @param \M2E\Core\Model\Connector\Response\Message[] $messages
     *
     * @return void
     */
    private function processFail(array $messages): void
    {
        $this->addTags($messages);
    }

    /**
     * @param \M2E\Core\Model\Connector\Response\Message[] $messages
     *
     * @return void
     */
    private function addTags(array $messages): void
    {
        $tags = [];

        if (empty($messages)) {
            $tags[] = $this->tagFactory->createWithHasErrorCode();
        }

        foreach ($messages as $message) {
            if (!$message->isSenderComponent() || empty($message->getCode())) {
                continue;
            }

            if ($message->isError()) {
                $tags[] = $this->tagFactory->createByErrorCode((string)$message->getCode(), $message->getText());
            }
        }

        if (!empty($tags)) {
            $tags[] = $this->tagFactory->createWithHasErrorCode();

            $this->tagBuffer->addTags($this->getListingProduct(), $tags);
            $this->tagBuffer->flush();
        }
    }

    private function generateResultMessage($data): void
    {

        if ($data['status'] === false) {
            $resultMessage = (string)__('Product was not created successfully.');
            $this->getLogBuffer()->addFail($resultMessage);

            return;
        }

        $currencyCode = $this->getListingProduct()->getListing()->getStorefront()->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);
        $onlineQty = $this->getListingProduct()->getOnlineQty();
        $onlinePrice = $this->getListingProduct()->getOnlineCurrentPrice();
        $message = (string)__(
            'Product was Listed with QTY %qty, Price %price',
            [
                'qty' => $onlineQty,
                'price' => $currency->toCurrency($onlinePrice),
            ]
        );
        $this->getLogBuffer()->addSuccess($message);
    }

    protected function getProductLockType(): string
    {
        return \M2E\Kaufland\Model\Product\Lock::TYPE_PRODUCT;
    }
}
