<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseProduct;

class Validator extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractValidator
{
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

        return true;
    }
}
