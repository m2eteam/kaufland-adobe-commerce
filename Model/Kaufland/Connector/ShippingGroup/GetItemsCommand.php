<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\ShippingGroup;

class GetItemsCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountServerHash;
    private string $storefrontCode;

    public function __construct(string $accountServerHash, string $storefrontCode)
    {
        $this->accountServerHash = $accountServerHash;
        $this->storefrontCode = $storefrontCode;
    }

    public function getCommand(): array
    {
        return ['shippingGroup', 'get', 'items'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountServerHash,
            'storefront' => $this->storefrontCode
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): Get\Response
    {
        $responseData = $response->getResponseData();

        $shippingGroups = [];
        foreach ($responseData['shipping_groups'] as $shippingGroup) {
            $shippingGroups[] = new \M2E\Kaufland\Model\Kaufland\Connector\Account\ShippingGroup(
                (int)$shippingGroup['id'],
                $shippingGroup['storefront'],
                $shippingGroup['name'],
                (bool)$shippingGroup['is_default'],
                $shippingGroup['type'],
                $shippingGroup['currency'],
                $shippingGroup['regions'] ?? []
            );
        }

        return new \M2E\Kaufland\Model\Kaufland\Connector\ShippingGroup\Get\Response(
            $shippingGroups
        );
    }
}
