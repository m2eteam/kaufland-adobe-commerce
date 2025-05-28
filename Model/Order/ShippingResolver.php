<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order;

class ShippingResolver
{
    public function getShippingDate(array $items)
    {
        $smallestDate = $items[0]['delivery_time_expires_date'];

        $smallestDateTime = \M2E\Core\Helper\Date::createDateGmt($smallestDate);

        foreach ($items as $item) {
            $currentDate = $item['delivery_time_expires_date'];
            $currentDateTime = \M2E\Core\Helper\Date::createDateGmt($currentDate);
            if ($currentDateTime < $smallestDateTime) {
                $smallestDate = $currentDate;
            }
        }

        return $smallestDate;
    }

    public function getShippingRate(array $items)
    {
        $rate = 0;
        foreach ($items as $item) {
            $rate += $item['shipping_rate'];
        }

        return $rate;
    }
}
