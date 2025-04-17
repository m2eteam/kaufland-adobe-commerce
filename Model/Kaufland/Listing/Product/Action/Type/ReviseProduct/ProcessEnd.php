<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseProduct;

class ProcessEnd extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Async\AbstractProcessEnd
{
    private \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\Kaufland\Model\Kaufland\TagFactory $tagFactory;
    private ResponseFactory $responseFactory;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseProduct\LoggerFactory $loggerFactory;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseProduct\LoggerFactory $loggerFactory,
        \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Kaufland\Model\Kaufland\TagFactory $tagFactory,
        ResponseFactory $responseFactory
    ) {
        $this->tagBuffer = $tagBuffer;
        $this->tagFactory = $tagFactory;
        $this->responseFactory = $responseFactory;
        $this->loggerFactory = $loggerFactory;
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
            $this->getStatusChanger(),
            $this->getRequestMetadata()
        );

        if (!$responseObj->isSuccess($data)) {
            $messages = $responseObj->getMessages($data);
            $this->addTags($messages);
            $this->addActionLogMessages($messages);

            return;
        }

        $logger = $this->loggerFactory->create();
        $logger->saveProductDataBeforeUpdate($this->getListingProduct());

        $responseObj->processSuccess($data);

        $messages = $responseObj->getMessages($data);
        if (!empty($messages)) {
            $this->addTags($messages);
            $this->addActionLogMessages($messages);
        }

        if ($data['status'] === false) {
            $resultMessage = (string)__('Item was not revised');
            $this->getLogBuffer()->addFail($resultMessage);

            return;
        }

        $logs = $logger->calculateLogs($this->getListingProduct());

        if (empty($logs)) {
            $this->getLogBuffer()->addSuccess('Item was revised');
        }

        foreach ($logs as $log) {
            $this->addActionLogMessage($log);
        }
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

    protected function getProductLockType(): string
    {
        return \M2E\Kaufland\Model\Product\Lock::TYPE_PRODUCT;
    }
}
