<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\ShippingGroup\Get;

class Processor
{
    private \M2E\Kaufland\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\Kaufland\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(\M2E\Kaufland\Model\Account $account, \M2E\Kaufland\Model\Storefront $storefront): Response
    {
        $command = new \M2E\Kaufland\Model\Kaufland\Connector\ShippingGroup\GetItemsCommand(
            $account->getServerHash(),
            $storefront->getStorefrontCode()
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
