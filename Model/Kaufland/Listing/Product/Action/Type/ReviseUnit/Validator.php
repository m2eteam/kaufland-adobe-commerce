<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseUnit;

class Validator extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractValidator
{
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ShippingHandlingTime $shippingHandlingTime;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ShippingHandlingTime $shippingHandlingTime
    ) {
        $this->shippingHandlingTime = $shippingHandlingTime;
    }

    public function validate(): bool
    {
        if (!$this->getListingProduct()->isRevisable()) {
            $this->addMessage('Item is not Listed or not available');

            return false;
        }

        if (empty($this->getListingProduct()->getKauflandProductId())) {
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

        if ($error = $this->shippingHandlingTime->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        return true;
    }
}
