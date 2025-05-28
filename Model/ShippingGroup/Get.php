<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ShippingGroup;

class Get
{
    private \M2E\Kaufland\Model\Channel\ShippingGroup\Processor $getProcessor;

    public function __construct(
        \M2E\Kaufland\Model\Channel\ShippingGroup\Processor $getProcessor
    ) {
        $this->getProcessor = $getProcessor;
    }

    /**
     * @return \M2E\Kaufland\Model\Channel\ShippingGroup\Item[]
     */
    public function getShippingGroups(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront
    ): array {
        $serverResponse = $this->getOnServer($account, $storefront);

        return $serverResponse->getShippingGroups();
    }

    private function getOnServer(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront
    ): \M2E\Kaufland\Model\Channel\Connector\ShippingGroup\Get\Response {

        return $this->getProcessor->process($account, $storefront);
    }
}
