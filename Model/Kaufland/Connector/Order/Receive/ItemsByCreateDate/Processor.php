<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Order\Receive\ItemsByCreateDate;

class Processor
{
    private \M2E\Kaufland\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\Kaufland\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    public function process(
        \M2E\Kaufland\Model\Account $account,
        \DateTimeInterface $createFrom,
        \DateTimeInterface $createTo
    ): \M2E\Kaufland\Model\Kaufland\Connector\Order\Receive\Response {
        $command = new \M2E\Kaufland\Model\Kaufland\Connector\Order\Receive\ItemsByCreateDateCommand(
            $account->getServerHash(),
            $createFrom,
            $createTo,
        );

        /** @var \M2E\Kaufland\Model\Kaufland\Connector\Order\Receive\Response */
        return $this->singleClient->process($command);
    }
}
