<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ListProduct;

use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\DataBuilder;

class Response extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractResponse
{
    use \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ResponseProductTrait;

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

        if (empty($responseUnits)) {
            if ($this->isProductCreated($response)) {
                $product->setKauflandProductId((string)$response['product']['id']);
                $this->productRepository->save($product);
            }
        } else {
            $storefront = $this->storefrontRepository->getByCode($responseUnits['storefront']);

            $product->setUnitId($responseUnits['unit_id'])
                    ->setStoreFrontId($storefront->getId())
                    ->setKauflandProductId((string)$responseUnits['product_id'])
                    ->setKauflandOfferId($responseUnits['offer_id'])
                    ->setStatusListed($this->getStatusChanger())
                    ->setOnlineQty($requestMetadata[DataBuilder\Qty::NICK]['qty'])
                    ->setOnlineCondition($responseUnits['condition'])
                    ->setOnlineWarehouse($responseUnits['warehouse_id'])
                    ->setOnlineShippingGroupId($responseUnits['shipping_group_id'])
                    ->setOnlineHandlingTime($responseUnits['handling_time'])
                    ->setOnlinePrice($requestMetadata[DataBuilder\Price::NICK]['amount']);

            $product->setOnlineTitle($requestMetadata[DataBuilder\Title::NICK]['online_title']);
            $product->setOnlineDescription($requestMetadata[DataBuilder\Description::NICK]['online_description']);
            $product->setOnlineImages($requestMetadata[DataBuilder\Images::NICK]['online_image']);
            $product->setOnlineCategoryId($requestMetadata[DataBuilder\Attributes::NICK]['online_category_id']);
            $product->setOnlineCategoryAttributesData($requestMetadata[DataBuilder\Attributes::NICK]['online_category_attribute_data']);
            $product->removeBlockingByError();

            $this->productRepository->save($product);
        }
    }

    public function isProductCreated(array $response): bool
    {
        return isset($response['product']['id']);
    }

    public function processSuccessOnlyProduct(array $response): void
    {
        $product = $this->getListingProduct();
        $product->setKauflandProductId((string)$response['product']['id']);
        $this->productRepository->save($product);
    }
}
