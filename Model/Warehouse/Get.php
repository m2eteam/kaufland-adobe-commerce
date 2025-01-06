<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Warehouse;

class Get
{
    private \M2E\Kaufland\Model\Kaufland\Connector\Warehouse\Get\Processor $getProcessor;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Connector\Warehouse\Get\Processor $getProcessor
    ) {
        $this->getProcessor = $getProcessor;
    }

    /**
     * @return \M2E\Kaufland\Model\Kaufland\Connector\Account\Warehouse[]
     */
    public function getWarehouses(\M2E\Kaufland\Model\Account $account): array
    {
        $serverResponse = $this->getOnServer($account);

        return $serverResponse->getWarehouses();
    }

    private function getOnServer(\M2E\Kaufland\Model\Account $account): \M2E\Kaufland\Model\Kaufland\Connector\Warehouse\Get\Response
    {
        return $this->getProcessor->process($account);
    }
}
