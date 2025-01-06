<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\Shipping;

class Diff extends \M2E\Kaufland\Model\ActiveRecord\Diff
{
    public function isDifferent(): bool
    {
        return $this->isShippingDifferent();
    }

    public function isShippingDifferent(): bool
    {
        $keys = [
            \M2E\Kaufland\Model\ResourceModel\Template\Shipping::COLUMN_HANDLING_TIME,
            \M2E\Kaufland\Model\ResourceModel\Template\Shipping::COLUMN_HANDLING_TIME_MODE,
            \M2E\Kaufland\Model\ResourceModel\Template\Shipping::COLUMN_HANDLING_TIME_ATTRIBUTE,
            \M2E\Kaufland\Model\ResourceModel\Template\Shipping::COLUMN_STOREFRONT_ID,
            \M2E\Kaufland\Model\ResourceModel\Template\Shipping::COLUMN_WAREHOUSE_ID,
            \M2E\Kaufland\Model\ResourceModel\Template\Shipping::COLUMN_SHIPPING_GROUP_ID,
        ];

        return $this->isSettingsDifferent($keys);
    }
}
