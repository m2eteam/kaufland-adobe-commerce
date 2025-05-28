<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\ListUnit;

use M2E\Kaufland\Model\Product\Action\Validator\ValidatorMessage;

class Validator extends \M2E\Kaufland\Model\Product\Action\Type\AbstractValidator
{
    private \M2E\Kaufland\Model\Product\Action\Validator\SameSkuAlreadyExists $sameSkuAlreadyExists;
    private \M2E\Kaufland\Model\Product\Action\Validator\ShippingHandlingTime $shippingHandlingTime;
    private \M2E\Kaufland\Model\Product\Action\Validator\ProductSkuExist $productSkuExist;

    public function __construct(
        \M2E\Kaufland\Model\Product\Action\Validator\SameSkuAlreadyExists $sameSkuAlreadyExists,
        \M2E\Kaufland\Model\Product\Action\Validator\ProductSkuExist $productSkuExist,
        \M2E\Kaufland\Model\Product\Action\Validator\ShippingHandlingTime $shippingHandlingTime
    ) {
        $this->sameSkuAlreadyExists = $sameSkuAlreadyExists;
        $this->productSkuExist = $productSkuExist;
        $this->shippingHandlingTime = $shippingHandlingTime;
    }
    public function validate(): bool
    {
        if (!$this->getListingProduct()->isListable()) {
            $this->addMessage(
                new ValidatorMessage(
                    'Item is Listed or not available',
                    \M2E\Kaufland\Model\Tag\ValidatorIssues::NOT_USER_ERROR
                )
            );

            return false;
        }

        if (!$this->getListingProduct()->getListing()->getTemplateShippingId()) {
            $this->addMessage(
                new ValidatorMessage(
                    'No Shipping policy is set for this M2E Listing. Please assign a Shipping policy to the Listing first.',
                    \M2E\Kaufland\Model\Tag\ValidatorIssues::ERROR_NO_SHIPPING_POLICY
                )
            );

            return false;
        }

        if (!$this->getListingProduct()->getListing()->getConditionValue()) {
            $this->addMessage(
                new ValidatorMessage(
                    'No Condition is set for this M2E Listing. Please assign a Condition to the Listing first.',
                    \M2E\Kaufland\Model\Tag\ValidatorIssues::ERROR_NO_CONDITION_SET
                )
            );

            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        if (!$this->validateQty()) {
            return false;
        }

        if ($error = $this->productSkuExist->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->sameSkuAlreadyExists->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->shippingHandlingTime->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if (empty($this->getListingProduct()->getKauflandProductId())) {
            return false;
        }

        return true;
    }
}
