<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\ListProduct;

class ValidatorFactory extends \M2E\Kaufland\Model\Product\Action\Type\AbstractValidatorFactory
{
    protected function getValidatorClass(): string
    {
        return Validator::class;
    }
}
