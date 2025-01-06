<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Attribute;

class GetCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $storefrontCode;
    private int $categoryId;

    public function __construct(string $accountHash, string $storefrontCode, int $categoryId)
    {
        $this->accountHash = $accountHash;
        $this->storefrontCode = $storefrontCode;
        $this->categoryId = $categoryId;
    }

    public function getCommand(): array
    {
        return ['category', 'get', 'attributes'];
    }

    public function getRequestData(): array
    {
        return [
            'storefront' => $this->storefrontCode,
            'account' => $this->accountHash,
            'category_id' => $this->categoryId,
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): Get\Response
    {
        $responseData = $response->getResponseData();

        $attributes = [];
        foreach ($responseData['attributes'] as $attributeData) {
            $attribute = new Attribute(
                $attributeData['id'],
                $attributeData['nick'],
                $attributeData['title'],
                $attributeData['description'],
                $attributeData['type'],
                $attributeData['is_required'],
                $attributeData['is_multiple_selected']
            );

            foreach ($attributeData['options'] as $option) {
                foreach ($option as $key => $value) {
                    $attribute->addOptions((string)$key, (string)$value);
                }
            }

            $attributes[] = $attribute;
        }

        return new \M2E\Kaufland\Model\Kaufland\Connector\Attribute\Get\Response(
            $attributes
        );
    }
}
