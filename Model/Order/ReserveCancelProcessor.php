<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order;

class ReserveCancelProcessor
{
    private \M2E\Kaufland\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\Kaufland\Model\Order\Repository $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param \M2E\Kaufland\Model\Account $account
     *
     * @return void
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function process(\M2E\Kaufland\Model\Account $account): void
    {
        foreach ($this->orderRepository->findForReleaseReservation($account) as $order) {
            /** @var \M2E\Kaufland\Model\Order $order */
            $order->getReserve()->release();
        }
    }
}
