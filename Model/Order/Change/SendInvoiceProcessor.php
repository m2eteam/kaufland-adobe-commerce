<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order\Change;

class SendInvoiceProcessor
{
    private const MAX_CHANGE_FOR_PROCESS = 50;

    private \M2E\Kaufland\Model\Order\Change\Repository $changeRepository;
    private \M2E\Kaufland\Model\Order\Repository $orderRepository;
    private \M2E\Kaufland\Model\Channel\Order\SendInvoice\Processor $sendInvoiceProcessor;
    private \Magento\Sales\Model\Order\Pdf\Invoice $pdfInvoice;

    public function __construct(
        \M2E\Kaufland\Model\Order\Change\Repository $changeRepository,
        \M2E\Kaufland\Model\Order\Repository $orderRepository,
        \M2E\Kaufland\Model\Channel\Order\SendInvoice\Processor $sendInvoiceProcessor,
        \Magento\Sales\Model\Order\Pdf\Invoice $pdfInvoice
    ) {
        $this->changeRepository = $changeRepository;
        $this->orderRepository = $orderRepository;
        $this->sendInvoiceProcessor = $sendInvoiceProcessor;
        $this->pdfInvoice = $pdfInvoice;
    }

    /**
     * @param \M2E\Kaufland\Model\Account $account
     *
     * @return void
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Zend_Pdf_Exception
     */
    public function process(\M2E\Kaufland\Model\Account $account): void
    {
        $this->deleteNotActualChanges();

        $changes = $this->changeRepository->findSendInvoiceForProcess(
            $account,
            self::MAX_CHANGE_FOR_PROCESS,
        );

        foreach ($changes as $change) {
            if (!$account->getInvoiceAndShipmentSettings()->isUploadMagentoInvoice()) {
                $this->removeChange($change);

                continue;
            }

            $order = $this->orderRepository->find($change->getOrderId());
            if ($order === null) {
                $this->removeChange($change);

                continue;
            }

            $magentoOrder = $order->getMagentoOrder();
            if ($magentoOrder === null) {
                return;
            }

            $params = $change->getParams();
            $invoiceId = (int)$params['invoice_id'];
            $requestInvoice = $this->getInvoice($order, $invoiceId);

            if ($requestInvoice === null) {
                $this->removeChange($change);

                continue;
            }

            $this->changeRepository->incrementAttemptCount([$change->getId()]);

            $response = $this->sendInvoiceProcessor->process($account, $order->getKauflandOrderId(), $requestInvoice);

            $this->removeChange($change);

            if (!$response->getMessageCollection()->hasErrors()) {
                $responseData = $response->getResponseData();
                $order->addSuccessLog(
                    '<a href="%url%" target="_blank">Invoice</a> was uploaded to the Order on Kaufland.',
                    ['url' => $responseData['url']],
                );

                continue;
            }

            foreach ($response->getMessageCollection()->getMessages() as $message) {
                if ($message->isError()) {
                    $order->addErrorLog(
                        'Invoice failed to be uploaded to an Order on Kaufland. Reason: %msg%',
                        ['msg' => $message->getText()],
                    );
                } else {
                    $order->addWarningLog($message->getText());
                }
            }
        }
    }

    protected function deleteNotActualChanges(): void
    {
        $this->changeRepository->deleteByProcessingAttemptCount(
            \M2E\Kaufland\Model\Order\Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
        );
    }

    private function removeChange(\M2E\Kaufland\Model\Order\Change $change): void
    {
        $this->changeRepository->delete($change);
    }

    /**
     * @param \M2E\Kaufland\Model\Order $order
     * @param int $invoiceId
     *
     * @return \M2E\Kaufland\Model\Channel\Order\SendInvoice\Invoice|null
     * @throws \Zend_Pdf_Exception
     */
    private function getInvoice(\M2E\Kaufland\Model\Order $order, int $invoiceId): ?\M2E\Kaufland\Model\Channel\Order\SendInvoice\Invoice
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoices */
        $invoices = $order->getMagentoOrder()->getInvoiceCollection();
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $invoices->getItemById($invoiceId);

        if ($invoice === null) {
            return null;
        }

        $pdf = $this->pdfInvoice->getPdf([$invoice]);
        $invoiceName = 'invoice_' . $invoice->getIncrementId() . '.pdf';
        $documentPdf = base64_encode($pdf->render());

        return new \M2E\Kaufland\Model\Channel\Order\SendInvoice\Invoice($invoiceName, $documentPdf);
    }
}
