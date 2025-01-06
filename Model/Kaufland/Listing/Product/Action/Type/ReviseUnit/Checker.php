<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseUnit;

class Checker
{
    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function isNeedReviseForQty(\M2E\Kaufland\Model\Product $listingProduct): bool
    {
        $synchronizationTemplate = $listingProduct->getSynchronizationTemplate();

        if (!$this->isQtyReviseEnabled($listingProduct)) {
            return false;
        }

        $isMaxAppliedValueModeOn = $synchronizationTemplate->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $synchronizationTemplate->getReviseUpdateQtyMaxAppliedValue();

        $productQty = $listingProduct->getQty();
        $channelQty = $listingProduct->getOnlineQty();

        // Check ReviseUpdateQtyMaxAppliedValue
        if (
            $isMaxAppliedValueModeOn
            && $productQty > $maxAppliedValue
            && $channelQty > $maxAppliedValue
        ) {
            return false;
        }

        return $productQty != $channelQty;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function isNeedReviseForPrice(\M2E\Kaufland\Model\Product $listingProduct): bool
    {
        if (!$this->isPriceReviseEnabled($listingProduct)) {
            return false;
        }

        return $listingProduct->getOnlineCurrentPrice() != $listingProduct->getFixedPrice();
    }

    public function isNeedReviseForShipping(\M2E\Kaufland\Model\Product $listingProduct): bool
    {
        $shippingData = $listingProduct->getShippingPolicyDataProvider();

        $kauflandShippingGroupId = $shippingData->getKauflandShippingGroupId();
        $kauflandWarehouseId = $shippingData->getKauflandWarehouseId();

        return ($listingProduct->getOnlineShippingGroupId() != $kauflandShippingGroupId)
            || ($listingProduct->getOnlineWarehouseId() != $kauflandWarehouseId)
            || ($listingProduct->getOnlineHandlingTime() != $shippingData->getHandlingTime());
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function isQtyReviseEnabled(\M2E\Kaufland\Model\Product $listingProduct): bool
    {
        $synchronizationTemplate = $listingProduct->getSynchronizationTemplate();

        return $synchronizationTemplate->isReviseUpdateQty();
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function isPriceReviseEnabled(\M2E\Kaufland\Model\Product $listingProduct): bool
    {
        $synchronizationTemplate = $listingProduct->getSynchronizationTemplate();

        return $synchronizationTemplate->isReviseUpdatePrice();
    }
}
