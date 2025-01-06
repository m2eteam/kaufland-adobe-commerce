<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Order;

class StatusResolver
{
    /**
     * Find supported order statuses by the link below; path: /Schemas/OrderUnitStatus.
     * @link https://sellerapi.kaufland.com/?page=endpoints
     */
    private const ORDER_STATUS_SENT_AND_AUTOPAID = 'sent_and_autopaid';
    private const ORDER_STATUS_SENT = 'sent';
    private const ORDER_STATUS_RETURNED_PAID = 'returned_paid';
    private const ORDER_STATUS_RETURNED = 'returned';
    private const ORDER_STATUS_RECEIVED = 'received';
    private const ORDER_STATUS_OPEN = 'open';
    private const ORDER_STATUS_NEED_TO_BE_SENT = 'need_to_be_sent';
    private const ORDER_STATUS_CANCELLED = 'cancelled';

    public function getOrderStatusResolver(array $items)
    {
        if (count($items) === 1) {
            return $this->convertKauflandOrderStatus($items[0]['status']);
        }

        $orderStatuses = [];
        foreach ($items as $item) {
            $orderStatuses[] = $this->convertKauflandOrderStatus($item['status']);
        }

        if (count(array_unique($orderStatuses)) === 1) {
            return $this->convertKauflandOrderStatus($items[0]['status']);
        }

        if (
            in_array(\M2E\Kaufland\Model\Order::STATUS_UNSHIPPED, $orderStatuses)
            && in_array(\M2E\Kaufland\Model\Order::STATUS_SHIPPED, $orderStatuses)
        ) {
            return \M2E\Kaufland\Model\Order::STATUS_SHIPPED_PARTIALLY;
        }

        if (in_array(\M2E\Kaufland\Model\Order::STATUS_RETURNED, $orderStatuses)) {
            return \M2E\Kaufland\Model\Order::STATUS_RETURNED_PARTIALLY;
        }

        if (in_array(\M2E\Kaufland\Model\Order::STATUS_CANCELED, $orderStatuses)) {
            return \M2E\Kaufland\Model\Order::STATUS_CANCELED_PARTIALLY;
        }

        return 'default_status';
    }

    public function convertKauflandOrderStatus(string $kauflandOrderStatus): int
    {
        $kauflandOrderStatus = mb_strtolower($kauflandOrderStatus);

        if (
            $kauflandOrderStatus === self::ORDER_STATUS_OPEN
        ) {
            return \M2E\Kaufland\Model\Order::STATUS_PENDING;
        }

        if (
            $kauflandOrderStatus === self::ORDER_STATUS_NEED_TO_BE_SENT
        ) {
            return \M2E\Kaufland\Model\Order::STATUS_UNSHIPPED;
        }

        if (
            $kauflandOrderStatus === self::ORDER_STATUS_SENT
            || $kauflandOrderStatus === self::ORDER_STATUS_SENT_AND_AUTOPAID
            || $kauflandOrderStatus === self::ORDER_STATUS_RECEIVED
        ) {
            return \M2E\Kaufland\Model\Order::STATUS_SHIPPED;
        }

        if (
            $kauflandOrderStatus === self::ORDER_STATUS_RETURNED
            || $kauflandOrderStatus === self::ORDER_STATUS_RETURNED_PAID
        ) {
            return \M2E\Kaufland\Model\Order::STATUS_RETURNED;
        }

        if ($kauflandOrderStatus === self::ORDER_STATUS_CANCELLED) {
            return \M2E\Kaufland\Model\Order::STATUS_CANCELED;
        }

        return \M2E\Kaufland\Model\Order::STATUS_UNKNOWN;
    }
}
