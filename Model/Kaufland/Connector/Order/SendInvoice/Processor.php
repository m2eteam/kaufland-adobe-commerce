<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Order\SendInvoice;

class Processor
{
    private \M2E\Kaufland\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\Kaufland\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    /**
     * @param \M2E\Kaufland\Model\Account $account
     * @param string $orderId
     * @param \M2E\Kaufland\Model\Kaufland\Connector\Order\SendInvoice\Invoice $invoice
     *
     * @return \M2E\Core\Model\Connector\Response
     * @throws \M2E\Kaufland\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     */
    public function process(
        \M2E\Kaufland\Model\Account $account,
        string $orderId,
        \M2E\Kaufland\Model\Kaufland\Connector\Order\SendInvoice\Invoice $invoice
    ): \M2E\Core\Model\Connector\Response {
        $command = new EntityCommand(
            $account->getServerHash(),
            $orderId,
            $invoice
        );

        /** @var \M2E\Core\Model\Connector\Response $response */
        $response = $this->singleClient->process($command);

        return $response;
    }
}
