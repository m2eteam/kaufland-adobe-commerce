<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\ListProduct;

class RequestFactory extends \M2E\Kaufland\Model\Product\Action\AbstractRequestFactory
{
    protected function getRequestClass(): string
    {
        return Request::class;
    }
}
