<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\Delete;

class Response extends \M2E\Kaufland\Model\Product\Action\Type\AbstractResponse
{
    private \M2E\Kaufland\Model\Product\RemoveHandler $removeHandlerFactory;

    public function __construct(
        \M2E\Kaufland\Model\Product\RemoveHandler $removeHandler
    ) {
        $this->removeHandlerFactory = $removeHandler;
    }

    public function processSuccess(array $response, array $responseParams = []): void
    {
        $listingProduct = $this->getListingProduct();
        $this->removeHandlerFactory->process($listingProduct);
    }
}
