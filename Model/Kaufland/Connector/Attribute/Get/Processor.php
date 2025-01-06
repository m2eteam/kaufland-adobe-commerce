<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Attribute\Get;

class Processor
{
    private \M2E\Kaufland\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\Kaufland\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront,
        int $categoryId
    ): Response {
        $command = new \M2E\Kaufland\Model\Kaufland\Connector\Attribute\GetCommand(
            $account->getServerHash(),
            $storefront->getStorefrontCode(),
            $categoryId,
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
