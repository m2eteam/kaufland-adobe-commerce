<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ListUnit;

use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\DataBuilder;

class Response extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractResponse
{
    private \M2E\Kaufland\Model\Product\Repository $productRepository;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;

    public function __construct(
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository
    ) {
        $this->productRepository = $productRepository;
        $this->storefrontRepository = $storefrontRepository;
    }

    public function processSuccess(array $response, array $responseParams = []): void
    {
        $requestMetadata = $this->getRequestMetaData();

        $product = $this->getListingProduct();
        $responseUnits = $response['unit'];

        $storefront = $this->storefrontRepository->getByCode($responseUnits['storefront']);

        $product->setUnitId($responseUnits['unit_id'])
                ->setStoreFrontId($storefront->getId())
                ->setKauflandProductId((string)$responseUnits['product_id'])
                ->setKauflandOfferId($responseUnits['offer_id'])
                ->setStatus(\M2E\Kaufland\Model\Product::STATUS_LISTED)
                ->setOnlineQty($requestMetadata[DataBuilder\Qty::NICK]['qty'])
                ->setOnlineCondition($responseUnits['condition'])
                ->setOnlineWarehouse($responseUnits['warehouse_id'])
                ->setOnlineShippingGroupId($responseUnits['shipping_group_id'])
                ->setOnlineHandlingTime($responseUnits['handling_time'])
                ->setOnlinePrice($requestMetadata[DataBuilder\Price::NICK]['amount'])
                ->removeBlockingByError();

        $this->productRepository->save($product);
    }
}
