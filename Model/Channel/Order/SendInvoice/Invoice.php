<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Order\SendInvoice;

class Invoice
{
    private string $name;
    private string $base64Data;

    public function __construct(
        string $name,
        string $base64Data
    ) {
        $this->name = $name;
        $this->base64Data = $base64Data;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBase64Data(): string
    {
        return $this->base64Data;
    }
}
