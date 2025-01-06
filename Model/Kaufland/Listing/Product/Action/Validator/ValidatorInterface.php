<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator;

interface ValidatorInterface
{
    public function validate(\M2E\Kaufland\Model\Product $product): ?string;
}
