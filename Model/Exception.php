<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model;

class Exception extends \Exception
{
    private array $additionalData;

    /**
     * @param string $message
     * @param array $additionalData
     * @param int $code
     */
    public function __construct(string $message = '', array $additionalData = [], int $code = 0)
    {
        $this->additionalData = $additionalData;

        parent::__construct($message, $code, null);
    }

    // ----------------------------------------

    /**
     * @return array
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}
