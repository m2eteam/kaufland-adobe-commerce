<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseUnit;

class Request extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractRequest
{
    public function getActionData(): array
    {
        $shippingData = $this->getListingProduct()->getShippingPolicyDataProvider();

        $kauflandShippingGroupId = $shippingData->getKauflandShippingGroupId();
        $kauflandWarehouseId = $shippingData->getKauflandWarehouseId();

        return [
            'storefront' => $this->getListing()->getStorefront()->getStorefrontCode(),
            'units' => [
                [
                    'unit_id' => $this->getListingProduct()->getUnitId(),
                    'listing_price' => $this->getPriceData(),
                    'amount' => $this->getQtyData(),
                    'handling_time' => $shippingData->getHandlingTime(),
                    'warehouse_id' => $kauflandWarehouseId,
                    'shipping_group_id' => $kauflandShippingGroupId,
                ],
            ],
        ];
    }
}
