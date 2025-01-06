<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ListUnit;

class Validator extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractValidator
{
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\SameSkuAlreadyExists $sameSkuAlreadyExists;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ShippingHandlingTime $shippingHandlingTime;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ProductSkuExist $productSkuExist;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\SameSkuAlreadyExists $sameSkuAlreadyExists,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ProductSkuExist $productSkuExist,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ShippingHandlingTime $shippingHandlingTime
    ) {
        $this->sameSkuAlreadyExists = $sameSkuAlreadyExists;
        $this->productSkuExist = $productSkuExist;
        $this->shippingHandlingTime = $shippingHandlingTime;
    }
    public function validate(): bool
    {
        if (!$this->getListingProduct()->isListable()) {
            $this->addMessage('Item is Listed or not available');

            return false;
        }

        if (!$this->getListingProduct()->getListing()->getTemplateShippingId()) {
            $this->addMessage('No Shipping policy is set for this M2E Listing. Please assign a Shipping policy to the Listing first.');

            return false;
        }

        if (!$this->getListingProduct()->getListing()->getConditionValue()) {
            $this->addMessage('No Condition is set for this M2E Listing. Please assign a Condition to the Listing first.');

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
