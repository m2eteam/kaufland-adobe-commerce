<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Dictionary\Attribute;

class Serializer
{
    private \M2E\Kaufland\Model\Category\Dictionary\AttributeFactory $attributeFactory;

    public function __construct(
        \M2E\Kaufland\Model\Category\Dictionary\AttributeFactory $attributeFactory
    ) {
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @param ProductAttribute[] $attributes
     *
     * @return string
     */
    public function serializeProductAttributes(array $attributes): string
    {
        $data = [];
        foreach ($attributes as $attribute) {
            $options = [];
            foreach ($attribute->getOptions() as $option) {
                $options[] = [
                    'value' => $option->getValue(),
                    'label' => $option->getLabel(),
                ];
            }

            $data[] = [
                'id' => $attribute->getId(),
                'nick' => $attribute->getNick(),
                'title' => $attribute->getTitle(),
                'description' => $attribute->getDescription(),
                'type' => $attribute->getType(),
                'is_required' => $attribute->isRequired(),
                'is_multiple_selected' => $attribute->isMultipleSelected(),
                'options' => $options,
            ];
        }

        return json_encode($data);
    }

    /**
     * @return ProductAttribute[]
     */
    public function unSerializeProductAttributes(string $jsonAttributes): array
    {
        $attributes = [];
        foreach (json_decode($jsonAttributes, true) as $item) {
            $options = [];
            foreach ($item['options'] as $option) {
                $options[] = $this->attributeFactory->createOption(
                    $option['value'],
                    $option['label']
                );
            }

            $attributes[] = $this->attributeFactory->createProductAttribute(
                $item['id'],
                $item['nick'],
                $item['title'],
                $item['description'],
                $item['type'],
                $item['is_required'],
                $item['is_multiple_selected'],
                $options
            );
        }

        return $attributes;
    }
}
