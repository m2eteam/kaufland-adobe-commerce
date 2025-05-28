<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Connector\ShippingGroup\Get;

class Response
{
    /** @var \M2E\Kaufland\Model\Channel\ShippingGroup\Item[] */
    private array $shippingGroups;

    public function __construct(array $shippingGroups)
    {
        $this->shippingGroups = $shippingGroups;
    }

    /**
     * @return \M2E\Kaufland\Model\Channel\ShippingGroup\Item[]
     */
    public function getShippingGroups(): array
    {
        return $this->shippingGroups;
    }
}
