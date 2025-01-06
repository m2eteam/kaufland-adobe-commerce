<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order;

class InvoiceService
{
    private \M2E\Kaufland\Model\Order\ChangeCreateService $changeCreateService;

    public function __construct(
        \M2E\Kaufland\Model\Order\ChangeCreateService $changeCreateService
    ) {
        $this->changeCreateService = $changeCreateService;
    }

    /**
     * @param \M2E\Kaufland\Model\Order $order
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param int $initiator
     *
     * @return void
     */
    public function processMagentoInvoice(
        \M2E\Kaufland\Model\Order $order,
        \Magento\Sales\Model\Order\Invoice $invoice,
        int $initiator
    ): void {
        \M2E\Core\Helper\Data::validateInitiator($initiator);

        if (!$this->canProcessMagentoInvoice($order)) {
            return;
        }

        $params = [
            'invoice_id' => $invoice->getId(),
        ];

        $this->changeCreateService->create(
            (int)$order->getId(),
            \M2E\Kaufland\Model\Order\Change::ACTION_SEND_INVOICE,
            $initiator,
            $params
        );
    }

    /**
     * @param \M2E\Kaufland\Model\Order $order
     *
     * @return bool
     */
    private function canProcessMagentoInvoice(\M2E\Kaufland\Model\Order $order): bool
    {
        if (!$order->getAccount()->getInvoiceAndShipmentSettings()->isUploadMagentoInvoice()) {
            return false;
        }

        $magentoOrder = $order->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if (!$magentoOrder->hasInvoices()) {
            return false;
        }

        return true;
    }
}
