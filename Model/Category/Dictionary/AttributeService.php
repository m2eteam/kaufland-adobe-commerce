<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Dictionary;

class AttributeService
{
    private \M2E\Kaufland\Model\Channel\Attribute\Retriever $attributeGetProcessor;
    private \M2E\Kaufland\Model\Category\Dictionary\AttributeFactory $attributeFactory;

    public function __construct(
        \M2E\Kaufland\Model\Channel\Attribute\Retriever $attributeGetProcessor,
        \M2E\Kaufland\Model\Category\Dictionary\AttributeFactory $attributeFactory
    ) {
        $this->attributeGetProcessor = $attributeGetProcessor;
        $this->attributeFactory = $attributeFactory;
    }

    public function getCategoryDataFromServer(
        \M2E\Kaufland\Model\Storefront $storefront,
        int $categoryId
    ): \M2E\Kaufland\Model\Channel\Connector\Attribute\Get\Response {
        return $this->attributeGetProcessor
            ->process($storefront->getAccount(), $storefront, $categoryId);
    }

    public function getAttributes(
        \M2E\Kaufland\Model\Channel\Connector\Attribute\Get\Response $categoryData
    ): array {
        $productAttributes = [];
        foreach ($categoryData->getAttributes() as $responseAttribute) {
            $options = [];
            foreach ($responseAttribute->getOptions() as $option) {
                $options[] = $this->attributeFactory->createOption(
                    $option['value'],
                    $option['label']
                );
            }

            $productAttributes[] = $this->attributeFactory->createProductAttribute(
                $responseAttribute->getId(),
                $responseAttribute->getNick(),
                $responseAttribute->getTitle(),
                $responseAttribute->getDescription(),
                $responseAttribute->getType(),
                $responseAttribute->isRequired(),
                $responseAttribute->isMultipleSelected(),
                $options
            );
        }

        return $productAttributes;
    }

    public function getHasRequiredAttributes(
        \M2E\Kaufland\Model\Channel\Connector\Attribute\Get\Response $categoryData
    ): bool {
        foreach ($categoryData->getAttributes() as $attribute) {
            if ($attribute->isRequired()) {
                return true;
            }
        }

        return false;
    }

    public function getTotalProductAttributes(
        \M2E\Kaufland\Model\Channel\Connector\Attribute\Get\Response $categoryData
    ): int {

        return count($categoryData->getAttributes());
    }
}
