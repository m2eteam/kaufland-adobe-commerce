<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Order\Receive\ItemsByUpdateDate;

class Processor
{
    private \M2E\Kaufland\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\Kaufland\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    public function process(
        \M2E\Kaufland\Model\Account $account,
        \DateTimeInterface $updateFrom,
        \DateTimeInterface $updateTo
    ): \M2E\Kaufland\Model\Kaufland\Connector\Order\Receive\Response {
        $command = new \M2E\Kaufland\Model\Kaufland\Connector\Order\Receive\ItemsByUpdateDateCommand(
            $account->getServerHash(),
            $updateFrom,
            $updateTo,
        );

        /** @var \M2E\Kaufland\Model\Kaufland\Connector\Order\Receive\Response */
        return $this->singleClient->process($command);
    }
}
