<?php

namespace M2E\Kaufland\Model\Product\Action\DataBuilder;

class Identifier extends AbstractDataBuilder
{
    public const NICK = 'Identifier';

    private \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration;

    private static array $identifierTypeMap = [
        \M2E\Core\Helper\Data\Product\Identifier::GTIN => 'GTIN',
        \M2E\Core\Helper\Data\Product\Identifier::EAN => 'EAN',
        \M2E\Core\Helper\Data\Product\Identifier::UPC => 'UPC',
        \M2E\Core\Helper\Data\Product\Identifier::ISBN => 'ISBN',
    ];

    public function __construct(
        \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration,
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper
    ) {
        parent::__construct($magentoAttributeHelper);

        $this->configuration = $configuration;
    }

    /**
     * @return array{id: string, type: string}
     */
    public function getBuilderData(): array
    {
        if (!$this->configuration->isIdentifierCodeModeCustomAttribute()) {
            return [];
        }

        $this->searchNotFoundAttributes();
        $attributeCode = $this->configuration->getIdentifierCodeCustomAttribute();
        $value = $this->getMagentoProduct()->getAttributeValue($attributeCode);
        if (empty($value)) {
            $this->processNotFoundAttributes('Product ID');

            return [];
        }

        $type = $this->getIdentifierType($value);
        if ($type === null) {
            $this->addWarningMessage('Product ID Type invalid');

            return [];
        }

        return [
            'id' => $value,
            'type' => $type,
        ];
    }

    private function getIdentifierType(string $identifierValue): ?string
    {
        $type = \M2E\Core\Helper\Data\Product\Identifier::getIdentifierType($identifierValue);
        if ($type === null) {
            return null;
        }

        return self::$identifierTypeMap[$type] ?? null;
    }
}
