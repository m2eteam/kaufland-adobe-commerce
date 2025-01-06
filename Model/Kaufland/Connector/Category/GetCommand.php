<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Category;

class GetCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $storefrontCode;

    public function __construct(string $storefrontCode)
    {
        $this->storefrontCode = $storefrontCode;
    }

    public function getCommand(): array
    {
        return ['category', 'get', 'list'];
    }

    public function getRequestData(): array
    {
        return [
            'storefront' => $this->storefrontCode,
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): Get\Response
    {
        $result = new Get\Response();
        $responseData = $response->getResponseData();

        foreach ($responseData['categories'] as $categoryData) {
            $result->addCategory(
                new Category(
                    $categoryData['id'],
                    $categoryData['parent_id'],
                    $categoryData['title'],
                )
            );
        }

        return $result;
    }
}
