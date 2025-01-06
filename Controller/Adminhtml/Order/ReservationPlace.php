<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Order;

use M2E\Kaufland\Controller\Adminhtml\AbstractOrder;

class ReservationPlace extends AbstractOrder
{
    private \M2E\Kaufland\Model\Order\Repository $orderRepository;
    private \M2E\Kaufland\Model\Order\ReservationService $reservationService;

    public function __construct(
        \M2E\Kaufland\Model\Order\Repository $orderRepository,
        \M2E\Kaufland\Model\Order\ReservationService $reservationService,
        $context = null
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->reservationService = $reservationService;
    }

    public function execute()
    {
        $ids = $this->getRequestIds();

        if (count($ids) == 0) {
            $this->messageManager->addError(__('Please select Order(s).'));
            $this->_redirect('*/*/index');

            return;
        }

        $orders = $this->orderRepository->findOrdersForReservationPlace($ids);
        $result = $this->reservationService->reservationPlace($orders);

        if ($result['success']) {
            $this->messageManager->addSuccess($result['message']);
        } else {
            $this->messageManager->addError($result['message']);
        }

        $this->_redirect($this->redirect->getRefererUrl());
    }
}
