<?php

namespace M2E\Kaufland\Model\Kaufland\Connector\Order\Units\Ship;

class Unit implements \JsonSerializable
{
    private int $orderUnitId;
    private string $carrierCode;
    private string $trackingNumber;

    public function __construct(
        int $orderUnitId,
        string $carrierCode,
        string $trackingNumber
    ) {
        $this->orderUnitId = $orderUnitId;
        $this->carrierCode = $carrierCode;
        $this->trackingNumber = $trackingNumber;
    }

    public function jsonSerialize(): array
    {
        return [
            'order_unit_id' => $this->orderUnitId,
            'carrier_code' => $this->carrierCode,
            'tracking_number' => $this->trackingNumber,
        ];
    }
}
