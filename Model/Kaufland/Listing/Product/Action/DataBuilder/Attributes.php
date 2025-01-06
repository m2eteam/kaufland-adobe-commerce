<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\DataBuilder;

use M2E\Kaufland\Model\Category\Dictionary;

class Attributes extends AbstractDataBuilder
{
    public const NICK = 'Attributes';
    private \M2E\Kaufland\Model\Category\Tree\Repository $categoryTreeRepository;
    private \M2E\Kaufland\Model\Category\Attribute\Repository $categoryAttributeRepository;
    private \M2E\Kaufland\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever;
    private int $onlineCategoryId;
    private string $onlineCategoriesAttributesData = '';

    public function __construct(
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\Kaufland\Model\Category\Tree\Repository $categoryTreeRepository,
        \M2E\Kaufland\Model\Category\Attribute\Repository $categoryAttributeRepository,
        \M2E\Kaufland\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever
    ) {
        parent::__construct($magentoAttributeHelper);
        $this->categoryTreeRepository = $categoryTreeRepository;
        $this->categoryAttributeRepository = $categoryAttributeRepository;
        $this->magentoAttributeRetriever = $magentoAttributeRetriever;
    }

    public function getBuilderData(): array
    {
        $storefrontId = $this->getListingProduct()->getListing()->getStorefront()->getId();

        $categoryDictionary = $this->getListingProduct()->getCategoryDictionary();
        $categoryId = $categoryDictionary->getCategoryId();
        $category = $this->categoryTreeRepository->getCategoryByStorefrontIdAndCategoryId(
            $storefrontId,
            $categoryId
        );

        $attributes = $this->getAttributesData($categoryDictionary->getId());

        $data = [
            "category" => [
                $category->getTitle(),
            ],
        ];

        $this->onlineCategoryId = $categoryId;
        ksort($attributes);
        $this->onlineCategoriesAttributesData = \M2E\Core\Helper\Data::md5String(json_encode($attributes));

        return array_merge($data, $attributes);
    }

    private function getAttributesData($categoryDictionaryId): array
    {
        $categoryAttributes = $this->categoryAttributeRepository->findByDictionaryId($categoryDictionaryId);

        $attributes = [];
        $value = '';
        foreach ($categoryAttributes as $attribute) {
            if ($attribute->isValueModeNone()) {
                unset($attribute[$attribute->getAttributeNick()]);
                continue;
            }

            if ($attribute->isValueModeRecommended()) {
                $value = $attribute->getRecommendedValue();
                if ($attribute->getAttributeType() !== Dictionary::RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT) {
                    $value = reset($value);
                    $attributes[$attribute->getAttributeNick()] = $value;
                } else {
                    $attributes[$attribute->getAttributeNick()] = $value;
                }

                continue;
            }

            if ($attribute->isValueModeCustomAttribute()) {
                $attributeCode = $attribute->getCustomAttributeValue();
                $magentoProduct = $this->getListingProduct()->getMagentoProduct();

                $magentoAttributeRetriever = $this->magentoAttributeRetriever->create($magentoProduct);
                $attributeVal = $magentoAttributeRetriever->tryRetrieve(
                    $attributeCode,
                    'Category Attribute'
                );
                if ($attributeVal !== null) {
                    $attributes[$attribute->getAttributeNick()] = $attributeVal;
                }

                $this->addNotFoundAttributesToWarning($magentoAttributeRetriever);
            }

            if ($attribute->isValueModeCustomValue()) {
                $value = $attribute->getCustomValue();
                $attributes[$attribute->getAttributeNick()] = $value;
            }
        }

        return $attributes;
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => [
                'online_category_id' => $this->onlineCategoryId,
                'online_category_attribute_data' => $this->onlineCategoriesAttributesData,
            ],
        ];
    }
}
