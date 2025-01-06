<?php

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Delete;

class Request extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractRequest
{
    public function getActionData(): array
    {
        $request = [
            'account' => $this->getListingProduct()->getAccount()->getServerHash(),
            'storefront' => $this->getListingProduct()->getStoreFront()->getStorefrontCode(),
            'unit_id' => $this->getListingProduct()->getUnitId(),
        ];

        return $request;
    }
}
