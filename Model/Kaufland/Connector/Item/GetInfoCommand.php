<?php

namespace M2E\Kaufland\Model\Kaufland\Connector\Item;

class GetInfoCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $kauflandProductId;
    private string $accountHash;

    public function __construct(string $kauflandProductId, string $accountHash)
    {
        $this->kauflandProductId = $kauflandProductId;
        $this->accountHash = $accountHash;
    }

    public function getCommand(): array
    {
        return ['inventory', 'get', 'items'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'product_id' => $this->kauflandProductId,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response {
        return $response;
    }
}
