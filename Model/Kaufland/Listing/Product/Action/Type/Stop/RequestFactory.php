<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Stop;

class RequestFactory extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\AbstractRequestFactory
{
    protected function getRequestClass(): string
    {
        return Request::class;
    }
}
