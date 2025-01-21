<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ListProduct;

class Validator extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractValidator
{
    private \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\SameSkuAlreadyExists $sameSkuAlreadyExists;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ProductSkuExist $productSkuExist;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ShippingHandlingTime $shippingHandlingTime;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\RequiredCategoryAttributesExist $requiredCategoryAttributesExist;

    public function __construct(
        \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\SameSkuAlreadyExists $sameSkuAlreadyExists,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ProductSkuExist $productSkuExist,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ShippingHandlingTime $shippingHandlingTime,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\RequiredCategoryAttributesExist $requiredCategoryAttributesExist
    ) {
        $this->configuration = $configuration;
        $this->sameSkuAlreadyExists = $sameSkuAlreadyExists;
        $this->productSkuExist = $productSkuExist;
        $this->shippingHandlingTime = $shippingHandlingTime;
        $this->requiredCategoryAttributesExist = $requiredCategoryAttributesExist;
    }

    public function validate(): bool
    {
        if (!$this->getListingProduct()->isListableAsProduct()) {
            $this->addMessage('Item is Listed or not available');

            return false;
        }

        if (!$this->getListingProduct()->getListing()->getTemplateDescriptionId()) {
            $this->addMessage(
                'No Description policy is set for this M2E Listing. Please assign a Description policy to the Listing first.'
            );

            return false;
        }

        if ($error = $this->requiredCategoryAttributesExist->validate($this->getListingProduct())) {
            $this->addMessage($error);

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

        if (!$this->validateEan()) {
            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        return true;
    }

    private function validateEan(): bool
    {
        $eanAttributeCode = $this->configuration->getIdentifierCodeCustomAttribute();
        $magentoProduct = $this->getListingProduct()->getMagentoProduct();
        if ($magentoProduct->getAttributeValue($eanAttributeCode)) {
            return true;
        } else {
            $message = (string)__('EAN is missing a value.');
            $this->addMessage(\M2E\Kaufland\Helper\Module\Log::encodeDescription($message));

            return false;
        }
    }
}
