<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Account\Update;

class Processor
{
    private \M2E\Kaufland\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\Kaufland\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(
        \M2E\Kaufland\Model\Account $account,
        string $title,
        string $clientKey,
        string $secretKey
    ): Response {
        $command = new \M2E\Kaufland\Model\Kaufland\Connector\Account\UpdateCommand(
            $account->getServerHash(),
            $title,
            $clientKey,
            $secretKey
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
