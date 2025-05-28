<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Validator;

interface ValidatorInterface
{
    public function validate(\M2E\Kaufland\Model\Product $product): ?ValidatorMessage;
}
