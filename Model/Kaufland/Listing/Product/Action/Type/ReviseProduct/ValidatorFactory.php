<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseProduct;

class ValidatorFactory extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractValidatorFactory
{
    protected function getValidatorClass(): string
    {
        return Validator::class;
    }
}
