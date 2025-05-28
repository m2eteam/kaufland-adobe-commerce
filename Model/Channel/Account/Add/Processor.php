<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Account\Add;

use M2E\Kaufland\Model\Channel\Connector\Account\Add\Response;

class Processor
{
    private \M2E\Kaufland\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\Kaufland\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(string $title, string $privateKey, string $secretKey): Response
    {
        $command = new \M2E\Kaufland\Model\Channel\Connector\Account\AddCommand($title, $privateKey, $secretKey);

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
