<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Validator;

class ProductSkuExist implements \M2E\Kaufland\Model\Product\Action\Validator\ValidatorInterface
{
    public function validate(\M2E\Kaufland\Model\Product $product): ?ValidatorMessage
    {
        $offerId = $product->getKauflandOfferId();
        if (empty($offerId)) {
            return new ValidatorMessage(
                (string)__('Product was not Listed. The SKU value is missing.'),
                \M2E\Kaufland\Model\Tag\ValidatorIssues::ERROR_SKU_MISSING
            );
        }

        return null;
    }
}
