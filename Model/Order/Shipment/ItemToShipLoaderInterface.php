<?php

namespace M2E\Kaufland\Model\Order\Shipment;

interface ItemToShipLoaderInterface
{
    /**
     * @return \M2E\Kaufland\Model\Order\Item[]
     */
    public function loadItem(): array;
}
