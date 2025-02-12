<?php

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Stop;

class Request extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractRequest
{
    public function getActionData(): array
    {
        $request = [
            'storefront' => $this->getListing()->getStorefront()->getStorefrontCode(),
            'units' => [
                [
                    'unit_id' => $this->getListingProduct()->getUnitId(),
                    'amount' => 0
                ]
            ],
        ];

        return $request;
    }
}
