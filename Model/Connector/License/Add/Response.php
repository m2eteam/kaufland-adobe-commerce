<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Connector\License\Add;

class Response
{
    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
