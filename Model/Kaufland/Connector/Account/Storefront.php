<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Account;

class Storefront
{
    private string $storefrontCode;

    public function __construct(
        string $storefrontCode
    ) {
        $this->storefrontCode = $storefrontCode;
    }

    public function getStorefrontCode(): string
    {
        return $this->storefrontCode;
    }
}
