<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Connector\Order\SendInvoice;

class EntityCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $orderId;
    private \M2E\Kaufland\Model\Channel\Order\SendInvoice\Invoice $invoice;

    public function __construct(
        string $accountHash,
        string $orderId,
        \M2E\Kaufland\Model\Channel\Order\SendInvoice\Invoice $invoice
    ) {
        $this->accountHash = $accountHash;
        $this->orderId = $orderId;
        $this->invoice = $invoice;
    }

    public function getCommand(): array
    {
        return ['order', 'send', 'invoice'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'order_id' => $this->orderId,
            'invoice' => [
                'name' => $this->invoice->getName(),
                'data' => $this->invoice->getBase64Data(),
            ],
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): object
    {
        return $response;
    }
}
