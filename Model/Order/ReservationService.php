<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order;

class ReservationService
{
    /**
     * @param \M2E\Kaufland\Model\Order[] $orders
     *
     * @return array
     */
    public function reservationCancel(array $orders): array
    {
        $actionSuccessful = false;

        try {
            foreach ($orders as $order) {
                $order->getLogService()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_USER);

                if ($order->getReserve()->cancel()) {
                    $actionSuccessful = true;
                }
            }

            if ($actionSuccessful) {
                return ['success' => true, 'message' => __('QTY reserve for selected Order(s) was canceled.')];
            } else {
                return ['success' => false, 'message' => __('QTY reserve for selected Order(s) was not canceled.')];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => __(
                    'QTY reserve for selected Order(s) was not canceled. Reason: %error_message',
                    ['error_message' => $e->getMessage()]
                ),
            ];
        }
    }

    /**
     * @param \M2E\Kaufland\Model\Order[] $orders
     *
     * @return array
     */
    public function reservationPlace(array $orders): array
    {
        $actionSuccessful = false;

        try {
            foreach ($orders as $order) {
                $order->getLogService()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_USER);

                if (!$order->isReservable()) {
                    continue;
                }

                if ($order->getReserve()->place()) {
                    $actionSuccessful = true;
                }
            }

            if ($actionSuccessful) {
                return ['success' => true, 'message' => __('QTY for selected Order(s) was reserved.')];
            } else {
                return ['success' => false, 'message' => __('QTY for selected Order(s) was not reserved.')];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => __(
                    'QTY for selected Order(s) was not reserved. Reason: %error_message',
                    ['error_message' => $e->getMessage()]
                ),
            ];
        }
    }
}
