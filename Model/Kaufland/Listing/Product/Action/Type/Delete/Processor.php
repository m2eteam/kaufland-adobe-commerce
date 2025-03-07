<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Delete;

use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractValidator;

class Processor extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\AbstractProcessor
{
    private \M2E\Kaufland\Model\Connector\Client\Single $serverClient;
    private ValidatorFactory $actionValidatorFactory;
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;

    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractValidator $actionValidator;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\RequestData $requestData;

    public function __construct(
        ValidatorFactory $actionValidatorFactory,
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory,
        \M2E\Kaufland\Model\Connector\Client\Single $serverClient
    ) {
        $this->serverClient = $serverClient;
        $this->actionValidatorFactory = $actionValidatorFactory;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
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

        $command = new \M2E\Kaufland\Model\Kaufland\Connector\Item\DeleteCommand(
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
        );

        $responseObj->processSuccess($response->getResponseData());

        return (string)__(
            'Item was removed. Unit ID %unit_id',
            ['unit_id' => $this->getListingProduct()->getUnitId()]
        );
    }

    protected function processFail(
        \M2E\Core\Model\Connector\Response\MessageCollection $responseMessageCollection
    ): void {
    }

    protected function getActionNick(): string
    {
        return \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\DefinitionsCollection::ACTION_UNIT_DELETE;
    }

    protected function getProductLockType(): string
    {
        return \M2E\Kaufland\Model\Product\Lock::TYPE_UNIT;
    }
}
