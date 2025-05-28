<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Connector\Storefront\Get;

class Response
{
    private array $storefronts;

    /**
     * @param \M2E\Kaufland\Model\Channel\Storefront\Item[] $storefronts
     */
    public function __construct(
        array $storefronts
    ) {
        $this->storefronts = $storefronts;
    }

    /**
     * @return \M2E\Kaufland\Model\Channel\Storefront\Item[]
     */
    public function getStorefronts(): array
    {
        return $this->storefronts;
    }
}
