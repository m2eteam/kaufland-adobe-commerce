<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category;

use M2E\Kaufland\Model\ResourceModel\Category\Attribute as AttributeResource;

class Attribute extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public const VALUE_MODE_NONE = 0;
    public const VALUE_MODE_RECOMMENDED = 1;
    public const VALUE_MODE_CUSTOM_VALUE = 2;
    public const VALUE_MODE_CUSTOM_ATTRIBUTE = 3;

    public const ATTRIBUTE_TYPE_CERTIFICATE = 'certificate';
    public const ATTRIBUTE_TYPE_PRODUCT = 'product';

    public function _construct()
    {
        parent::_construct();
        $this->_init(AttributeResource::class);
    }

    public function create(
        int $dictionaryId,
        string $attributeType,
        string $attributeId,
        string $attributeNick,
        string $attributeTitle,
        string $attributeDescription,
        int $valueMode,
        array $recommendedValues,
        string $customValue,
        string $customAttributeValue
    ): self {
        $this->setCategoryDictionaryId($dictionaryId);
        $this->setAttributeType($attributeType);
        $this->setAttributeId($attributeId);
        $this->setAttributeNick($attributeNick);
        $this->setAttributeTitle($attributeTitle);
        $this->setAttributeDescription($attributeDescription);
        $this->setValueMode($valueMode);
        $this->setRecommendedValue($recommendedValues);
        $this->setCustomValue($customValue);
        $this->setCustomAttributeValue($customAttributeValue);

        return $this;
    }

    public function setCategoryDictionaryId(int $categoryDictionaryId): void
    {
        $this->setData(AttributeResource::COLUMN_CATEGORY_DICTIONARY_ID, $categoryDictionaryId);
    }

    public function getCategoryDictionaryId(): int
    {
        return (int)$this->getData(AttributeResource::COLUMN_CATEGORY_DICTIONARY_ID);
    }

    public function setAttributeType(string $type): void
    {
        $this->setData(AttributeResource::COLUMN_ATTRIBUTE_TYPE, $type);
    }

    public function getAttributeType(): string
    {
        return (string)$this->getData(AttributeResource::COLUMN_ATTRIBUTE_TYPE);
    }

    public function setAttributeId(string $id): void
    {
        $this->setData(AttributeResource::COLUMN_ATTRIBUTE_ID, $id);
    }

    public function getAttributeId(): string
    {
        return $this->getData(AttributeResource::COLUMN_ATTRIBUTE_ID);
    }

    public function setAttributeTitle(string $title): void
    {
        $this->setData(AttributeResource::COLUMN_ATTRIBUTE_TITLE, $title);
    }

    public function getAttributeTitle(): string
    {
        return (string)$this->getData(AttributeResource::COLUMN_ATTRIBUTE_NICK);
    }

    public function setAttributeNick(string $nick): void
    {
        $this->setData(AttributeResource::COLUMN_ATTRIBUTE_NICK, $nick);
    }

    public function getAttributeNick(): string
    {
        return (string)$this->getData(AttributeResource::COLUMN_ATTRIBUTE_NICK);
    }
    public function setAttributeDescription(string $description): void
    {
        $this->setData(AttributeResource::COLUMN_ATTRIBUTE_DESCRIPTION, $description);
    }

    public function getAttributeDescription(): string
    {
        return (string)$this->getData(AttributeResource::COLUMN_ATTRIBUTE_DESCRIPTION);
    }

    public function setValueCustomAttributeMode(): void
    {
        $this->setValueMode(self::VALUE_MODE_CUSTOM_ATTRIBUTE);
    }

    public function setValueMode(int $mode): void
    {
        $this->setData(AttributeResource::COLUMN_VALUE_MODE, $mode);
    }

    public function getValueMode(): int
    {
        return (int)$this->getData(AttributeResource::COLUMN_VALUE_MODE);
    }

    public function setRecommendedValue(array $recommendedValue): void
    {
        $this->setData(
            AttributeResource::COLUMN_VALUE_RECOMMENDED,
            json_encode($recommendedValue, JSON_THROW_ON_ERROR)
        );
    }

    public function getRecommendedValue(): array
    {
        $recommendedValue = $this->getData(AttributeResource::COLUMN_VALUE_RECOMMENDED);
        if (empty($recommendedValue)) {
            return [];
        }

        return json_decode($recommendedValue, true);
    }

    public function setCustomValue(string $customValue): void
    {
        $this->setData(AttributeResource::COLUMN_VALUE_CUSTOM_VALUE, $customValue);
    }

    public function getCustomValue(): string
    {
        return (string)$this->getData(AttributeResource::COLUMN_VALUE_CUSTOM_VALUE);
    }

    public function setCustomAttributeValue(string $customAttribute): void
    {
        $this->setData(AttributeResource::COLUMN_VALUE_CUSTOM_ATTRIBUTE, $customAttribute);
    }

    public function getCustomAttributeValue(): string
    {
        return (string)$this->getData(AttributeResource::COLUMN_VALUE_CUSTOM_ATTRIBUTE);
    }

    // ----------------------------------------

    public function isValueModeNone(): bool
    {
        return $this->getValueMode() === self::VALUE_MODE_NONE;
    }

    public function isValueModeRecommended(): bool
    {
        return $this->getValueMode() === self::VALUE_MODE_RECOMMENDED;
    }

    public function isValueModeCustomAttribute(): bool
    {
        return $this->getValueMode() === self::VALUE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function isValueModeCustomValue(): bool
    {
        return $this->getValueMode() === self::VALUE_MODE_CUSTOM_VALUE;
    }
}
