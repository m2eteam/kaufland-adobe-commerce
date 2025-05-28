<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\ReviseProduct;

class Request extends \M2E\Kaufland\Model\Product\Action\Type\AbstractRequest
{
    private \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration;

    public function __construct(
        \M2E\Kaufland\Model\Product\Action\DataBuilder\Factory $dataBuilderFactory,
        \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration
    ) {
        parent::__construct($dataBuilderFactory);

        $this->configuration = $configuration;
    }

    public function getActionData(): array
    {
        $listing = $this->getListingProduct()->getListing();

        $eanAttributeCode = $this->configuration->getIdentifierCodeCustomAttribute();
        $magentoProduct = $this->getListingProduct()->getMagentoProduct();
        $ean = $magentoProduct->getAttributeValue($eanAttributeCode);
        $attributes = $this->getAttributesData();

        $request =  [
            'storefront' => $listing->getStorefront()->getStorefrontCode(),
            'product' => [
                "ean" => [
                    $ean,
                ],
                "attributes" => [
                    "title" => [
                        $this->getTitleData(),
                    ],
                    "description" => [
                        $this->getDescriptionData(),
                    ],
                    "picture" => $this->getImagesData(),
                ],
            ],
        ];

        foreach ($attributes as $key => $value) {
            $request['product']['attributes'][$key] = $value;
        }
        return $request;
    }
}
