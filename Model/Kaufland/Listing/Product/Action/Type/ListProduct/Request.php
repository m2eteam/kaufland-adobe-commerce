<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ListProduct;

class Request extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractRequest
{
    private \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\DataBuilder\Factory $dataBuilderFactory,
        \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration
    ) {
        parent::__construct($dataBuilderFactory);
        $this->configuration = $configuration;
    }

    public function getActionData(): array
    {
        $listing = $this->getListingProduct()->getListing();

        $shippingData = $this->getListingProduct()->getShippingPolicyDataProvider();
        $kauflandShippingGroupId = $shippingData->getKauflandShippingGroupId();
        $kauflandWarehouseId = $shippingData->getKauflandWarehouseId();

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
            'unit' => [
                'product_id' => (int)$this->getListingProduct()->getKauflandProductId(),
                'offer_id' => $this->getListingProduct()->getKauflandOfferId(),
                'listing_price' => $this->getPriceData(),
                'amount' => $this->getQtyData(),
                'handling_time' => $shippingData->getHandlingTime(),
                'warehouse_id' => $kauflandWarehouseId,
                'shipping_group_id' => $kauflandShippingGroupId,
                'note' => '', // todo get from listing setting
                'condition' => $listing->getConditionValue(),
            ],
        ];

        foreach ($attributes as $key => $value) {
            $request['product']['attributes'][$key] = $value;
        }
        return $request;
    }
}
