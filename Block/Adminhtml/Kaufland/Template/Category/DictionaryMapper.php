<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category;

use M2E\Kaufland\Model\AttributeMapping\Gpsr\Pair;
use M2E\Kaufland\Model\Category\Attribute;

class DictionaryMapper
{
    private \M2E\Kaufland\Model\Category\Attribute\Repository $attributeRepository;
    private \M2E\Kaufland\Model\AttributeMapping\GpsrService $gpsrService;
    private \M2E\Kaufland\Model\AttributeMapping\GeneralService $generalService;

    /** @var \M2E\Core\Model\AttributeMapping\Pair[] */
    private array $generalAttributeMapping;
    /** @var \M2E\Core\Model\AttributeMapping\Pair[] */
    private array $gpsrAttributeMapping;

    public function __construct(
        \M2E\Kaufland\Model\Category\Attribute\Repository $attributeRepository,
        \M2E\Kaufland\Model\AttributeMapping\GpsrService $gpsrService,
        \M2E\Kaufland\Model\AttributeMapping\GeneralService $generalService
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->gpsrService = $gpsrService;
        $this->generalService = $generalService;
    }

    /**
     * @see \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Chooser\Specific\Form\Element\Dictionary
     */
    public function getProductAttributes(
        \M2E\Kaufland\Model\Category\Dictionary $dictionary
    ): array {
        $gpsrAttributes = $this->getGpsrAttributesByAttributeCode();
        $generalAttributes = $this->getGeneralAttributesMappingByCode();
        $mappingAttributes = array_replace($gpsrAttributes, $generalAttributes);
        $savedAttributes = $this->loadSavedAttributes($dictionary, [
            Attribute::ATTRIBUTE_TYPE_PRODUCT,
        ]);

        $attributes = [];
        foreach ($dictionary->getProductAttributes() as $productAttribute) {
            $item = $this->map($productAttribute, $savedAttributes, $mappingAttributes);

            if ($item['required']) {
                array_unshift($attributes, $item);
                continue;
            }

            $attributes[] = $item;
        }

        return $this->sortAttributesByTitle($attributes);
    }

    /**
     * @param \M2E\Kaufland\Model\Category\Dictionary\AbstractAttribute $attribute
     * @param \M2E\Kaufland\Model\Category\Attribute[] $savedAttributes
     * @param \M2E\Core\Model\AttributeMapping\Pair[] $mappingAttributesByCode
     *
     * @return array
     */
    private function map(
        \M2E\Kaufland\Model\Category\Dictionary\AbstractAttribute $attribute,
        array $savedAttributes,
        array $mappingAttributesByCode
    ): array {
        if (
            $attribute->getType() === \M2E\Kaufland\Model\Category\Dictionary::RENDER_TYPE_TEXT
            && $attribute->isMultipleSelected()
        ) {
            $type = \M2E\Kaufland\Model\Category\Dictionary::RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT;
        } else {
            $type = $attribute->getType();
        }

        $item = [
            'id' => $attribute->getId(),
            'title' => $attribute->getTitle(),
            'nick' => $attribute->getNick(),
            'description' => $attribute->getDescription(),
            'attribute_type' => $type,
            'required' => $attribute->isRequired(),
            'min_values' => $attribute->isRequired() ? 1 : 0,
            'max_values' => $attribute->isMultipleSelected() ? count($attribute->getOptions()) : 1,
            'values' => [],
            'template_attribute' => [],
        ];

        $existsAttribute = $savedAttributes[$attribute->getId()] ?? null;
        $attributeMapping = $mappingAttributesByCode[$attribute->getNick()] ?? null;
        if (
            $existsAttribute !== null
            || $attributeMapping !== null
        ) {
            $item['template_attribute'] = [
                'id' => $existsAttribute ? $existsAttribute->getAttributeId() : null,
                'template_category_id' => $existsAttribute ? $existsAttribute->getId() : null,
                'mode' => '1',
                'attribute_title' => $existsAttribute ? $existsAttribute->getAttributeId() : $attribute->getTitle(),
                'value_mode' => $existsAttribute !== null
                    ? $existsAttribute->getValueMode()
                    : ($attributeMapping !== null ? \M2E\Kaufland\Model\Category\Attribute::VALUE_MODE_CUSTOM_ATTRIBUTE : \M2E\Kaufland\Model\Category\Attribute::VALUE_MODE_NONE),
                'value_kaufland_recommended' => $existsAttribute ? $existsAttribute->getRecommendedValue() : null,
                'value_custom_value' => $existsAttribute ? $existsAttribute->getCustomValue() : null,
                'value_custom_attribute' => $existsAttribute !== null
                    ? $existsAttribute->getCustomAttributeValue()
                    : ($attributeMapping !== null ? $attributeMapping->getMagentoAttributeCode() : null),
            ];
        }

        foreach ($attribute->getOptions() as $option) {
            $item['options'][] = [
                'value' => $option->getValue(),
                'label' => $option->getLabel(),
            ];
        }

        return $item;
    }

    /**
     * @param \M2E\Kaufland\Model\Category\Dictionary $dictionary
     * @param array $typeFilter
     *
     * @return \M2E\Kaufland\Model\Category\Attribute[]
     */
    private function loadSavedAttributes(
        \M2E\Kaufland\Model\Category\Dictionary $dictionary,
        array $typeFilter = []
    ): array {
        $attributes = [];

        $savedAttributes = $this
            ->attributeRepository
            ->findByDictionaryId($dictionary->getId(), $typeFilter);

        foreach ($savedAttributes as $attribute) {
            $attributes[$attribute->getAttributeId()] = $attribute;
        }

        return $attributes;
    }

    public function sortAttributesByTitle(array $attributes): array
    {
        usort($attributes, function ($prev, $next) {
            return strcmp($prev['title'], $next['title']);
        });

        $requiredAttributes = [];
        foreach ($attributes as $index => $attribute) {
            if (isset($attribute['required']) && $attribute['required'] === true) {
                $requiredAttributes[] = $attribute;
                unset($attributes[$index]);
            }
        }

        return array_merge($requiredAttributes, $attributes);
    }

    /**
     * @return \M2E\Core\Model\AttributeMapping\Pair[]
     */
    private function getGpsrAttributesByAttributeCode(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->gpsrAttributeMapping)) {
            return $this->gpsrAttributeMapping;
        }

        $result = $this->gpsrService->getAllGroupedByCode();

        return $this->gpsrAttributeMapping = $result;
    }

    /**
     * @return \M2E\Core\Model\AttributeMapping\Pair[]
     */
    private function getGeneralAttributesMappingByCode(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->generalAttributeMapping)) {
            return $this->generalAttributeMapping;
        }

        $result = [];
        foreach ($this->generalService->getAll() as $item) {
            $result[$item->getChannelAttributeCode()] = $item;
        }

        return $this->generalAttributeMapping = $result;
    }
}
