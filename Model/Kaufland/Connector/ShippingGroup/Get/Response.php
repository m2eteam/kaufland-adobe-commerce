<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\ShippingGroup\Get;

class Response
{
    /** @var \M2E\Kaufland\Model\Kaufland\Connector\Account\ShippingGroup[] */
    private array $shippingGroups;

    public function __construct(array $shippingGroups)
    {
        $this->shippingGroups = $shippingGroups;
    }

    /**
     * @return \M2E\Kaufland\Model\Kaufland\Connector\Account\ShippingGroup[]
     */
    public function getShippingGroups(): array
    {
        return $this->shippingGroups;
    }
}
