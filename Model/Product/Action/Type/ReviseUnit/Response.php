<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\ReviseUnit;

use M2E\Kaufland\Model\Product\Action\Type\ListUnit\Request;

class Response extends \M2E\Kaufland\Model\Product\Action\Type\AbstractResponse
{
    use \M2E\Kaufland\Model\Product\Action\Type\ResponseUnitTrait;

    private \M2E\Kaufland\Model\Product\Repository $repository;

    public function __construct(\M2E\Kaufland\Model\Product\Repository $repository)
    {
        $this->repository = $repository;
    }

    public function processSuccess(array $response, array $responseParams = []): void
    {
        /** @see Request::getActionData() */
        $requestData = $this->getRequestData();

        $requestMetadata = $this->getRequestMetaData();

        $product = $this->getListingProduct();

        $product->setOnlinePrice($requestMetadata[\M2E\Kaufland\Model\Product\Action\DataBuilder\Price::NICK]['amount'])
                ->setOnlineQty($requestMetadata[\M2E\Kaufland\Model\Product\Action\DataBuilder\Qty::NICK]['qty'])
                ->setOnlineWarehouse($requestData['units'][0]['warehouse_id'])
                ->setOnlineShippingGroupId($requestData['units'][0]['shipping_group_id'])
                ->setOnlineHandlingTime($requestData['units'][0]['handling_time'])
                ->removeBlockingByError();

        $this->repository->save($product);
    }
}
