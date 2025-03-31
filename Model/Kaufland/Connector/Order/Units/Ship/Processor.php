<?php

namespace M2E\Kaufland\Model\Kaufland\Connector\Order\Units\Ship;

class Processor
{
    private \M2E\Kaufland\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\Kaufland\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    /**
     * @param \M2E\Kaufland\Model\Kaufland\Connector\Order\Units\Ship\Unit[] $packages
     *
     * @throws \M2E\Kaufland\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     */
    public function process(
        \M2E\Kaufland\Model\Account $account,
        array $packages
    ): \M2E\Kaufland\Model\Kaufland\Connector\Order\Units\Ship\Response {
        $command = new \M2E\Kaufland\Model\Kaufland\Connector\Order\Units\ShipCommand(
            $account,
            $packages
        );

        /** @var \M2E\Kaufland\Model\Kaufland\Connector\Order\Units\Ship\Response */
        return $this->singleClient->process($command);
    }
}
