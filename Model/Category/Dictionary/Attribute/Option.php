<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Dictionary\Attribute;

class Option
{
    private string $value;
    private string $label;

    public function __construct(string $value, string $label)
    {
        $this->value = $value;
        $this->label = $label;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
