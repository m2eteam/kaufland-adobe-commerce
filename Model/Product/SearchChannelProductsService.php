<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

use M2E\Kaufland\Model\Kaufland\Connector\Product\Search\SearchByEanCommand;

class SearchChannelProductsService
{
    private \M2E\Kaufland\Model\Connector\Client\Single $serverClient;

    public function __construct(
        \M2E\Kaufland\Model\Connector\Client\Single $serverClient
    ) {
        $this->serverClient = $serverClient;
    }

    /**
     * @param \M2E\Kaufland\Model\Account $account
     * @param \M2E\Kaufland\Model\Storefront $storefront
     * @param array $eans
     *
     * @return \M2E\Kaufland\Model\Kaufland\Connector\Product\Search\Product[]
     * @throws \M2E\Kaufland\Model\Exception
     * @throws \M2E\Kaufland\Model\Exception\Connection
     */
    public function findByEans(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront,
        array $eans
    ): array {
        $result = [];
        foreach (array_chunk($eans, SearchByEanCommand::MAX_EAN_FOR_REQUEST) as $eanPack) {
            $command = new SearchByEanCommand(
                $account->getServerHash(),
                $storefront->getStorefrontCode(),
                $eanPack
            );

            /** @var \M2E\Kaufland\Model\Kaufland\Connector\Product\Search\Response $response */
            $response = $this->serverClient->process($command);
            if (empty($response->getProducts())) {
                continue;
            }

            array_push($result, ...$response->getProducts());
        }

        return $result;
    }
}
