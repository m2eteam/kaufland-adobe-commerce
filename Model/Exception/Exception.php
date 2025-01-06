<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Exception;

class Exception extends \Exception
{
    private array $additionalData;

    public function __construct(string $message = '', array $additionalData = [], int $code = 0)
    {
        $this->additionalData = $additionalData;

        parent::__construct($message, $code, null);
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}
