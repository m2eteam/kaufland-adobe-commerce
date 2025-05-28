<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\ReviseProduct;

use M2E\Kaufland\Model\Product\Action\Type\ListUnit\Request;

class Response extends \M2E\Kaufland\Model\Product\Action\Type\AbstractResponse
{
    use \M2E\Kaufland\Model\Product\Action\Type\ResponseProductTrait;

    private \M2E\Kaufland\Model\Product\Repository $repository;

    public function __construct(
        \M2E\Kaufland\Model\Product\Repository $repository
    ) {
        $this->repository = $repository;
    }

    public function processSuccess(array $response, array $responseParams = []): void
    {
        /** @see Request::getActionData() */
        $requestMetadata = $this->getRequestMetaData();

        $product = $this->getListingProduct();

        $product->setOnlineTitle($requestMetadata[\M2E\Kaufland\Model\Product\Action\DataBuilder\Title::NICK]['online_title']);
        $product->setOnlineDescription($requestMetadata[\M2E\Kaufland\Model\Product\Action\DataBuilder\Description::NICK]['online_description']);
        $product->setOnlineImages($requestMetadata[\M2E\Kaufland\Model\Product\Action\DataBuilder\Images::NICK]['online_image']);
        $product->setOnlineCategoryId($requestMetadata[\M2E\Kaufland\Model\Product\Action\DataBuilder\Attributes::NICK]['online_category_id']);
        $product->setOnlineCategoryAttributesData($requestMetadata[\M2E\Kaufland\Model\Product\Action\DataBuilder\Attributes::NICK]['online_category_attribute_data']);
        $product->removeBlockingByError();
        $product->makeComplete();

        $this->repository->save($product);
    }
}
