<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Warehouse;

class GetItemsCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountServerHash;

    public function __construct(string $accountServerHash)
    {
        $this->accountServerHash = $accountServerHash;
    }

    public function getCommand(): array
    {
        return ['warehouse', 'get', 'items'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountServerHash
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): Get\Response
    {
        $responseData = $response->getResponseData();

        $warehouses = [];
        foreach ($responseData['warehouses'] as $warehouse) {
            $warehouses[] = new \M2E\Kaufland\Model\Kaufland\Connector\Account\Warehouse(
                (int)$warehouse['id'],
                $warehouse['name'],
                (bool)$warehouse['is_default'],
                $warehouse['type'],
                isset($warehouse['address']) ? $warehouse['address'] : []
            );
        }

        return new \M2E\Kaufland\Model\Kaufland\Connector\Warehouse\Get\Response(
            $warehouses
        );
    }
}
