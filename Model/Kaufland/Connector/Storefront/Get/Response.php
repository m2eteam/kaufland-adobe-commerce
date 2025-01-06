<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Storefront\Get;

class Response
{
    private array $storefronts;

    /**
     * @param \M2E\Kaufland\Model\Kaufland\Connector\Account\Storefront[] $storefronts
     */
    public function __construct(
        array $storefronts
    ) {
        $this->storefronts = $storefronts;
    }

    /**
     * @return \M2E\Kaufland\Model\Kaufland\Connector\Account\Storefront[]
     */
    public function getStorefronts(): array
    {
        return $this->storefronts;
    }
}
