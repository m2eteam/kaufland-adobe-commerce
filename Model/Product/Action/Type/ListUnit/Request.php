<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\ListUnit;

class Request extends \M2E\Kaufland\Model\Product\Action\Type\AbstractRequest
{
    public function getActionData(): array
    {
        $listing = $this->getListingProduct()->getListing();
        $shippingData = $this->getListingProduct()->getShippingPolicyDataProvider();

        $kauflandShippingGroupId = $shippingData->getKauflandShippingGroupId();
        $kauflandWarehouseId = $shippingData->getKauflandWarehouseId();

        return [
            'storefront' => $listing->getStorefront()->getStorefrontCode(),
            'unit' => [
                'product_id' => (int)$this->getListingProduct()->getKauflandProductId(),
                'offer_id' => $this->getListingProduct()->getKauflandOfferId(),
                'listing_price' => $this->getPriceData(),
                'amount' => $this->getQtyData(),
                'handling_time' => $shippingData->getHandlingTime(),
                'warehouse_id' => $kauflandWarehouseId,
                'shipping_group_id' => $kauflandShippingGroupId,
                'note' => '', // todo get from listing setting
                'condition' => $listing->getConditionValue(),
            ],
        ];
    }
}
