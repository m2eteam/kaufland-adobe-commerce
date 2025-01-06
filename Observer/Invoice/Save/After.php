<?php

declare(strict_types=1);

namespace M2E\Kaufland\Observer\Invoice\Save;

class After extends \M2E\Kaufland\Observer\AbstractObserver
{
    private \M2E\Kaufland\Model\Order\Repository $repository;
    private \M2E\Kaufland\Model\Order\InvoiceService $invoiceService;

    public function __construct(
        \M2E\Kaufland\Model\Order\Repository $repository,
        \M2E\Kaufland\Model\Order\InvoiceService $invoiceService,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory
    ) {
        parent::__construct($activeRecordFactory, $modelFactory);
        $this->repository = $repository;
        $this->invoiceService = $invoiceService;
    }

    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $this->getEvent()->getInvoice();
        $magentoOrderId = (int)$invoice->getOrderId();

        $order = $this->repository->findByMagentoOrderId($magentoOrderId);
        if ($order === null) {
            return;
        }

        $this->invoiceService->processMagentoInvoice($order, $invoice, \M2E\Core\Helper\Data::INITIATOR_EXTENSION);
    }
}
