<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Connector\Order\Cancel;

class EntityCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private array $orderItemIds;
    private string $reason;

    public function __construct(
        string $accountHash,
        array $orderItemIds,
        string $reason
    ) {
        $this->accountHash = $accountHash;
        $this->orderItemIds = $orderItemIds;
        $this->reason = $reason;
    }

    public function getCommand(): array
    {
        return ['order', 'cancel', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'order' => [
                'order_unit_ids' => $this->orderItemIds,
            ],
            'reason' => $this->reason,
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): object
    {
        return $response;
    }
}
