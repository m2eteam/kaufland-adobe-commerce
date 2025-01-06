<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ShippingGroup;

class Get
{
    private \M2E\Kaufland\Model\Kaufland\Connector\ShippingGroup\Get\Processor $getProcessor;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Connector\ShippingGroup\Get\Processor $getProcessor
    ) {
        $this->getProcessor = $getProcessor;
    }

    /**
     * @return \M2E\Kaufland\Model\Kaufland\Connector\Account\ShippingGroup[]
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
    ): \M2E\Kaufland\Model\Kaufland\Connector\ShippingGroup\Get\Response {

        return $this->getProcessor->process($account, $storefront);
    }
}
