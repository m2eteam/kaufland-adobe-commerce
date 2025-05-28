<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Account\Update;

use M2E\Kaufland\Model\Channel\Connector\Account\Update\Response;

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
        $command = new \M2E\Kaufland\Model\Channel\Connector\Account\UpdateCommand(
            $account->getServerHash(),
            $title,
            $clientKey,
            $secretKey
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
