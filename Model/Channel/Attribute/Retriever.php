<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Attribute;

use M2E\Kaufland\Model\Channel\Connector\Attribute\Get\Response;

class Retriever
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
        $command = new \M2E\Kaufland\Model\Channel\Connector\Attribute\GetCommand(
            $account->getServerHash(),
            $storefront->getStorefrontCode(),
            $categoryId,
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
