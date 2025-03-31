<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order\Change;

class CancelProcessor
{
    private const MAX_CHANGE_FOR_PROCESS = 50;

    private \M2E\Kaufland\Model\Order\Change\Repository $changeRepository;
    private \M2E\Kaufland\Model\Order\Repository $orderRepository;
    private \M2E\Kaufland\Model\Kaufland\Connector\Order\Cancel\Processor $channelCancelProcessor;

    public function __construct(
        \M2E\Kaufland\Model\Order\Change\Repository $changeRepository,
        \M2E\Kaufland\Model\Order\Repository $orderRepository,
        \M2E\Kaufland\Model\Kaufland\Connector\Order\Cancel\Processor $channelCancelProcessor
    ) {
        $this->changeRepository = $changeRepository;
        $this->orderRepository = $orderRepository;
        $this->channelCancelProcessor = $channelCancelProcessor;
    }

    /**
     * @param \M2E\Kaufland\Model\Account $account
     *
     * @return void
     * @throws \M2E\Kaufland\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function process(\M2E\Kaufland\Model\Account $account): void
    {
        $this->deleteNotActualChanges();

        $changes = $this->changeRepository->findCanceledReadyForProcess(
            $account,
            self::MAX_CHANGE_FOR_PROCESS,
        );

        $reason = $account->getOrdersSettings()->getOrderCancelOnChannelReason();

        foreach ($changes as $change) {
            if (!$account->getOrdersSettings()->isOrderCancelOrRefundOnChannelEnabled()) {
                $this->removeChange($change);

                continue;
            }

            $order = $this->orderRepository->find($change->getOrderId());
            if ($order === null) {
                $this->removeChange($change);

                continue;
            }

            if (!$order->canCancel()) {
                $this->removeChange($change);

                continue;
            }

            $orderItemIds = [];
            $params = $change->getParams();
            foreach ($params['items'] as $item) {
                $orderItem = $order->findItem((int)$item['id']);
                if ($orderItem === null) {
                    continue;
                }
                $orderItemIds[] = $orderItem->getKauflandOrderItemId();
            }

            if (empty($orderItemIds)) {
                $this->removeChange($change);

                continue;
            }

            $this->changeRepository->incrementAttemptCount([$change->getId()]);

            $response = $this->channelCancelProcessor->process($account, $orderItemIds, $reason);

            $this->removeChange($change);

            if (!$response->getMessageCollection()->hasErrors()) {
                $order->addSuccessLog('Order was cancelled on Kaufland.');

                continue;
            }

            foreach ($response->getMessageCollection()->getMessages() as $message) {
                if ($message->isError()) {
                    $order->addErrorLog(
                        'Order failed to be canceled on Kaufland. Reason: %msg%',
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
}
