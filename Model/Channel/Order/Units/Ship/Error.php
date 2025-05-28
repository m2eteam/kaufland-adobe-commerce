<?php

namespace M2E\Kaufland\Model\Channel\Order\Units\Ship;

class Error
{
    private int $orderUnitId;
    private string $message;

    public function __construct(
        int $orderUnitId,
        string $message
    ) {
        $this->orderUnitId = $orderUnitId;
        $this->message = $message;
    }

    public function getOrderUnitId(): int
    {
        return $this->orderUnitId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
