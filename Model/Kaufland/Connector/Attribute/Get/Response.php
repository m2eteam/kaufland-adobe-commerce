<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Attribute\Get;

class Response
{
    /** @var \M2E\Kaufland\Model\Kaufland\Connector\Attribute\Attribute[] */
    private array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return \M2E\Kaufland\Model\Kaufland\Connector\Attribute\Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
