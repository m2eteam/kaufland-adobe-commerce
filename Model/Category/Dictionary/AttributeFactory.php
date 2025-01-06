<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Dictionary;

class AttributeFactory
{
    /**
     * @param \M2E\Kaufland\Model\Category\Dictionary\Attribute\Option[] $options
     */
    public function createProductAttribute(
        int $id,
        string $nick,
        string $title,
        string $description,
        string $type,
        bool $isRequired,
        bool $isMultipleSelected,
        array $options
    ): \M2E\Kaufland\Model\Category\Dictionary\Attribute\ProductAttribute {
        return new \M2E\Kaufland\Model\Category\Dictionary\Attribute\ProductAttribute(
            $id,
            $nick,
            $title,
            $description,
            $type,
            $isRequired,
            $isMultipleSelected,
            $options
        );
    }

    public function createOption(string $value, string $label): \M2E\Kaufland\Model\Category\Dictionary\Attribute\Option
    {
        return new \M2E\Kaufland\Model\Category\Dictionary\Attribute\Option($value, $label);
    }
}
