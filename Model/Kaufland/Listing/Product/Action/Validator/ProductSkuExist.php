<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator;

class ProductSkuExist implements \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ValidatorInterface
{
    public function validate(\M2E\Kaufland\Model\Product $product): ?string
    {
        $offerId = $product->getKauflandOfferId();
        if (empty($offerId)) {
            return (string)__(
                'Product was not Listed. The SKU value is missing.'
            );
        }

        return null;
    }
}
