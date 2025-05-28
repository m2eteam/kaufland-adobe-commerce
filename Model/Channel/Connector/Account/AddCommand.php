<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Connector\Account;

class AddCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    public const PRODUCTION_MODE = 'production';

    private string $clientKey;
    private string $secretKey;
    private string $title;

    public function __construct(string $title, string $clientKey, string $secretKey)
    {
        $this->title = $title;
        $this->clientKey = $clientKey;
        $this->secretKey = $secretKey;
    }

    public function getCommand(): array
    {
        return ['account', 'add', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'title' => $this->title,
            'client_key' => $this->clientKey,
            'secret_key' => $this->secretKey,
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): \M2E\Kaufland\Model\Channel\Connector\Account\Add\Response
    {
        $responseData = $response->getResponseData();

        $storefronts = [];
        foreach ($responseData['account']['storefronts'] as $storefrontCode) {
            $storefronts[] = new \M2E\Kaufland\Model\Channel\Storefront\Item($storefrontCode);
        }

        $warehouses = [];
        foreach ($responseData['warehouses'] as $warehouse) {
            $warehouses[] = new \M2E\Kaufland\Model\Channel\Warehouse\Item(
                (int)$warehouse['warehouse_id'],
                $warehouse['name'],
                (bool)$warehouse['is_default'],
                $warehouse['type'],
                isset($warehouse['address']) ? $warehouse['address'] : []
            );
        }

        $shippingGroups = [];
        foreach ($responseData['shipping_groups'] as $shippingGroup) {
            $shippingGroups[] = new \M2E\Kaufland\Model\Channel\ShippingGroup\Item(
                (int)$shippingGroup['shipping_group_id'],
                $shippingGroup['storefront'],
                $shippingGroup['name'],
                (bool)$shippingGroup['is_default'],
                $shippingGroup['type'],
                $shippingGroup['currency'],
                $warehouse['regions'] ?? []
            );
        }

        return new \M2E\Kaufland\Model\Channel\Connector\Account\Add\Response(
            $responseData['hash'],
            $responseData['account']['identifier'],
            $storefronts,
            $warehouses,
            $shippingGroups
        );
    }
}
