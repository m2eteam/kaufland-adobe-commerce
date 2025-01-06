<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\InventorySync\Processing\Connector;

class InventoryGetItemsCommand implements \M2E\Core\Model\Connector\CommandProcessingInterface
{
    private string $accountServerHash;
    private string $storefront;

    public function __construct(string $accountServerHash, string $storefront)
    {
        $this->accountServerHash = $accountServerHash;
        $this->storefront = $storefront;
    }

    public function getCommand(): array
    {
        return ['Inventory', 'Get', 'Items'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountServerHash,
            'storefront' => $this->storefront,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response\Processing {
        return new \M2E\Core\Model\Connector\Response\Processing($response->getResponseData()['processing_id']);
    }
}
