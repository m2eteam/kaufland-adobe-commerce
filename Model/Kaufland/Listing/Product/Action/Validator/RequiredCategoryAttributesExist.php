<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator;

use M2E\Kaufland\Model\Category\Dictionary\AbstractAttribute as DictionaryAbstractAttribute;

class RequiredCategoryAttributesExist implements \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ValidatorInterface
{
    private \M2E\Kaufland\Model\Category\Attribute\Repository $attributeRepository;

    public function __construct(
        \M2E\Kaufland\Model\Category\Attribute\Repository $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    public function validate(\M2E\Kaufland\Model\Product $product): ?string
    {
        $productCategoryDictionary = $product->getCategoryDictionary();
        $magentoProduct = $product->getMagentoProduct();

        if (!$productCategoryDictionary->getHasRequiredProductAttributes()) {
            return null;
        }

        // ----------------------------------------

        $productAttributes = $productCategoryDictionary->getProductAttributes();

        $requiredAttributes = array_filter(
            $productAttributes,
            fn(DictionaryAbstractAttribute $attribute) => $attribute->isRequired()
        );

        $requiredAttributeIds = [];
        foreach ($requiredAttributes as $attribute) {
            $requiredAttributeIds[$attribute->getId()] = $attribute;
        }

        // ----------------------------------------

        $attributes = $this->attributeRepository->findByDictionaryIdAndAttributeIds(
            $productCategoryDictionary->getId(),
            array_keys($requiredAttributeIds)
        );

        $notValidAttributesLabels = [];
        foreach ($attributes as $attribute) {
            if (!$attribute->isValueModeCustomAttribute()) {
                continue;
            }

            $attributeCode = $attribute->getCustomAttributeValue();

            if (empty($magentoProduct->getAttributeValue($attributeCode))) {
                $notValidAttributesLabels[] = [
                    'kaufland_label' => $requiredAttributeIds[$attribute->getAttributeId()]->getTitle(),
                    'magento_label' => $magentoProduct->getAttributeStoreLabel($attributeCode)
                ];
            }
        }

        if (!empty($notValidAttributesLabels)) {
            return (string)__(
                '%kaufland_attributes is a required field%plural. The corresponding
                Magento attribute%plural %magento_attributes is either missing or does not have a value in the product.',
                [
                    'plural' => count($notValidAttributesLabels) >= 2 ? 's' : '',
                    'kaufland_attributes' => implode(', ', array_map(function ($notValidAttribute) {
                        return $notValidAttribute['kaufland_label'];
                    }, $notValidAttributesLabels)),
                    'magento_attributes' => implode(', ', array_map(function ($notValidAttribute) {
                        return $notValidAttribute['magento_label'];
                    }, $notValidAttributesLabels))
                ]
            );
        }

        return null;
    }
}
