<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Connector\Product\Search;

class SearchByEanCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    public const MAX_EAN_FOR_REQUEST = 10;

    private string $accountServerHash;
    private string $storefrontCode;
    private array $ean;

    public function __construct(string $accountServerHash, string $storefrontCode, array $ean)
    {
        $this->accountServerHash = $accountServerHash;
        $this->storefrontCode = $storefrontCode;
        $this->ean = $ean;
        if (\count($this->ean) > self::MAX_EAN_FOR_REQUEST) {
            throw new \LogicException('Ean pack so big');
        }
    }

    public function getCommand(): array
    {
        return ['Product', 'Search', 'ByEans'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountServerHash,
            'storefront' => $this->storefrontCode,
            'eans' => $this->ean
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): Response
    {
        $responseData = $response->getResponseData();

        $products = [];
        foreach ($responseData['products'] as $product) {
            $products[] = new Product(
                (string)$product['id'],
                $product['eans'],
                $product['category_title'],
                $product['category_id'],
            );
        }

        return new \M2E\Kaufland\Model\Channel\Connector\Product\Search\Response(
            $products
        );
    }
}
