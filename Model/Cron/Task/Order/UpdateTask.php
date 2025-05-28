<?php

namespace M2E\Kaufland\Model\Cron\Task\Order;

use M2E\Kaufland\Model\Order\Change;

class UpdateTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/update';

    private const MAX_UPDATES_PER_TIME = 50;

    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\Channel\Order\Units\Ship\Processor $orderShipProcessor;
    /** @var \M2E\Kaufland\Model\Order\Change\Repository */
    private Change\Repository $changeRepository;
    private \M2E\Kaufland\Model\Order\Repository $orderRepository;

    private array $bufferChangesByOrders = [];

    public function __construct(
        \M2E\Kaufland\Model\Channel\Order\Units\Ship\Processor $orderShipProcessor,
        \M2E\Kaufland\Model\Account\Repository                 $accountRepository,
        \M2E\Kaufland\Model\Order\Repository                   $orderRepository,
        \M2E\Kaufland\Model\Order\Change\Repository            $changeRepository
    ) {
        $this->accountRepository = $accountRepository;
        $this->orderShipProcessor = $orderShipProcessor;
        $this->changeRepository = $changeRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param \M2E\Kaufland\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\Kaufland\Model\Synchronization\Log::TASK_ORDERS);

        $this->deleteNotActualChanges();

        $permittedAccounts = $this->getPermittedAccounts();

        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $account) {
            $context->getOperationHistory()->addText('Starting Account "' . $account->getTitle() . '"');

            try {
                $this->processAccount($account);
            } catch (\Exception $exception) {
                $message = (string)\__(
                    'The "Update" Action for Account "%1" was completed with error.',
                    $account->getTitle(),
                );

                $context->getExceptionHandler()->processTaskAccountException($message, __FILE__, __LINE__);
                $context->getExceptionHandler()->processTaskException($exception);
            }
        }
    }

    //########################################

    /**
     * @return \M2E\Kaufland\Model\Account[]
     */
    protected function getPermittedAccounts(): array
    {
        return $this->accountRepository->getAll();
    }

    // ---------------------------------------

    protected function processAccount(\M2E\Kaufland\Model\Account $account): void
    {
        $changes = $this->changeRepository->findShippingForProcess($account, self::MAX_UPDATES_PER_TIME);
        if (empty($changes)) {
            return;
        }

        $this->processChanges($account, $changes);
    }

    // ---------------------------------------

    /**
     * @param \M2E\Kaufland\Model\Order\Change[] $changes
     *
     * @throws \M2E\Kaufland\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    protected function processChanges(
        \M2E\Kaufland\Model\Account $account,
        array $changes
    ): void {
        $this->prepareChangesBuffer();

        $ordersByKauflandOrderId = [];

        $changesIdsForIncrement = [];
        $packages = [];
        foreach ($changes as $change) {
            $changesIdsForIncrement[] = $change->getId();
            $order = $this->orderRepository->find($change->getOrderId());
            if ($order === null) {
                $this->removeChange($change);
                continue;
            }

            $this->addChangeToBuffer($order, $change);

            foreach ($this->buildItems($change->getParams()) as $item) {
                $orderItemId = (int)$item['item_id'];

                $orderItem = $this->orderRepository->findItemById($orderItemId);
                if ($orderItem === null) {
                    continue;
                }

                $ordersByKauflandOrderId[$orderItem->getKauflandOrderItemId()] = $order;

                $packages[] = new \M2E\Kaufland\Model\Channel\Order\Units\Ship\Unit(
                    (int)$orderItem->getKauflandOrderItemId(),
                    $item['carrier_code'],
                    $item['tracking_number'],
                );
            }
        }

        if (empty($packages)) {
            $this->removeAll($changes);

            return;
        }

        $this->changeRepository->incrementAttemptCount($changesIdsForIncrement);

        $response = $this->orderShipProcessor->process($account, $packages);

        $ordersWithLogs = [];
        foreach ($response->getErrors() as $error) {
            $order = $ordersByKauflandOrderId[$error->getOrderUnitId()] ?? null;
            if ($order === null) {
                continue;
            }

            if (!isset($ordersWithLogs[$order->getId()])) {
                $message = __(
                    'Shipping order error. Reason: %reason',
                    ['reason' => $error->getMessage()],
                );

                $order->addErrorLog($message);
            }

            $ordersWithLogs[$order->getId()] = true;

            unset($ordersByKauflandOrderId[$error->getOrderUnitId()]);
        }

        // only success
        foreach ($ordersByKauflandOrderId as $order) {
            $orderId = $order->getId();

            if (isset($ordersWithLogs[$orderId])) {
                continue;
            }

            ['tracking_number' => $trackingNumber, 'carrier_code' => $carrierCode] = $this->findTackingDataForOrder(
                $order
            );

            $this->removeChangesByOrder($order);

            $successMessage = __(
                'Order status was updated to Shipped. Tracking number %tracking for %carrier has been sent to %channel_title',
                [
                    'tracking' => $trackingNumber,
                    'carrier' => $carrierCode,
                    'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                ],
            );

            $order->addSuccessLog($successMessage);

            $ordersWithLogs[$orderId] = true;
        }
    }

    protected function deleteNotActualChanges(): void
    {
        $this->changeRepository->deleteByProcessingAttemptCount(
            \M2E\Kaufland\Model\Order\Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
        );
    }

    private function buildItems(array $changeParams): array
    {
        $oldCarrierCode = $changeParams['carrier_code'] ?? null;
        $oldTrackingNumber = $changeParams['tracking_number'] ?? null;

        $result = [];
        foreach ($changeParams['items'] as $itemData) {
            $result[] = [
                'item_id' => $itemData['item_id'],
                'carrier_code' => $itemData['carrier_code'] ?? $oldCarrierCode,
                'tracking_number' => $itemData['tracking_number'] ?? $oldTrackingNumber,
            ];
        }

        return $result;
    }

    private function removeAll(array $changes): void
    {
        foreach ($changes as $change) {
            $this->removeChange($change);
        }
    }

    private function removeChange(Change $change): void
    {
        $this->changeRepository->remove($change);
    }

    // ----------------------------------------

    private function prepareChangesBuffer(): void
    {
        $this->bufferChangesByOrders = [];
    }

    private function addChangeToBuffer(\M2E\Kaufland\Model\Order $order, Change $change): void
    {
        if (!isset($this->bufferChangesByOrders[$change->getOrderId()])) {
            $this->bufferChangesByOrders[$change->getOrderId()] = [];
        }

        $this->bufferChangesByOrders[$order->getId()][] = $change;
    }

    private function removeChangesByOrder(\M2E\Kaufland\Model\Order $order): void
    {
        foreach ($this->bufferChangesByOrders[$order->getId()] ?? [] as $change) {
            $this->changeRepository->remove($change);
        }

        unset($this->bufferChangesByOrders[$order->getId()]);
    }

    private function findTackingDataForOrder(\M2E\Kaufland\Model\Order $order): array
    {
        $firstChange = reset($this->bufferChangesByOrders[$order->getId()]);
        $firstItem = $this->buildItems($firstChange->getParams())[0];

        return [
            'tracking_number' => $firstItem['tracking_number'],
            'carrier_code' => $firstItem['carrier_code'],
        ];
    }
}
