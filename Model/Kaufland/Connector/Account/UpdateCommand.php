<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Account;

use M2E\Kaufland\Model\Kaufland\Connector\Account\AddCommand;

class UpdateCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $clientKey;
    private string $secretKey;
    private string $title;

    public function __construct(string $accountHash, string $title, string $clientKey, string $secretKey)
    {
        $this->accountHash = $accountHash;
        $this->title = $title;
        $this->clientKey = $clientKey;
        $this->secretKey = $secretKey;
    }

    public function getCommand(): array
    {
        return ['account', 'update', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'title' => $this->title,
            'client_key' => $this->clientKey,
            'secret_key' => $this->secretKey,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Kaufland\Model\Kaufland\Connector\Account\Update\Response {
        $responseData = $response->getResponseData();

        $storefronts = [];
        foreach ($responseData['account']['storefronts'] as $storefrontCode) {
            $storefronts[] = new \M2E\Kaufland\Model\Kaufland\Connector\Account\Storefront($storefrontCode);
        }

        $warehouses = [];
        foreach ($responseData['warehouses'] as $warehouse) {
            $warehouses[] = new \M2E\Kaufland\Model\Kaufland\Connector\Account\Warehouse(
                (int)$warehouse['warehouse_id'],
                $warehouse['name'],
                (bool)$warehouse['is_default'],
                $warehouse['type'],
                $warehouse['address'] ?? []
            );
        }

        $shippingGroups = [];
        foreach ($responseData['shipping_groups'] as $shippingGroup) {
            $shippingGroups[] = new \M2E\Kaufland\Model\Kaufland\Connector\Account\ShippingGroup(
                (int)$shippingGroup['shipping_group_id'],
                $shippingGroup['storefront'],
                $shippingGroup['name'],
                (bool)$shippingGroup['is_default'],
                $shippingGroup['type'],
                $shippingGroup['currency'],
                $warehouse['regions'] ?? []
            );
        }

        return new \M2E\Kaufland\Model\Kaufland\Connector\Account\Update\Response(
            $responseData['account']['identifier'],
            $storefronts,
            $warehouses,
            $shippingGroups
        );
    }
}
