<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Connector\Attribute\Get;

class Response
{
    /** @var \M2E\Kaufland\Model\Channel\Attribute\Item[] */
    private array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return \M2E\Kaufland\Model\Channel\Attribute\Item[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
