<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Item;

class DeleteCommand implements \M2E\Core\Model\Connector\CommandInterface
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
        return ['unit', 'delete', 'Entity'];
    }

    public function getRequestData(): array
    {
        return $this->requestData + ['account' => $this->accountHash];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response {
        return $response;
    }
}
