<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Delete;

class ResponseFactory extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractResponseFactory
{
    protected function getResponseClass(): string
    {
        return Response::class;
    }
}
