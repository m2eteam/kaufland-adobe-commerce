<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Item;

class ListProductCommand implements \M2E\Core\Model\Connector\CommandProcessingInterface
{
    private string $accountHash;
    private array $requestData;

    public function __construct(string $accountHash, array $requestData)
    {
        $this->accountHash = $accountHash;
        $this->requestData = $requestData;
    }

    public function getCommand(): array
    {
        return ['product', 'create', 'entity'];
    }

    public function getRequestData(): array
    {
        return $this->requestData + ['account' => $this->accountHash];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response\Processing {
        return new \M2E\Core\Model\Connector\Response\Processing($response->getResponseData()['processing_id']);
    }
}
