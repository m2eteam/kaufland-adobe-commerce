<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Storefront;

class GetStorefronts implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountServerHash;

    public function __construct(string $accountServerHash)
    {
        $this->accountServerHash = $accountServerHash;
    }

    public function getCommand(): array
    {
        return ['storefront', 'get', 'items'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountServerHash
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): Get\Response
    {
        $data = $response->getResponseData();

        $responses = [];
        foreach ($data['storefronts'] as $storefrontData) {
            $responses[] = new \M2E\Kaufland\Model\Kaufland\Connector\Account\Storefront($storefrontData);
        }

        return new \M2E\Kaufland\Model\Kaufland\Connector\Storefront\Get\Response(
            $responses
        );
    }
}
