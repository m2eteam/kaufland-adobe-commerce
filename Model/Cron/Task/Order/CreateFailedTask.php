<?php

namespace M2E\Kaufland\Model\Cron\Task\Order;

use M2E\Kaufland\Model\Cron\Task\Order\CreatorFactory;

class CreateFailedTask extends \M2E\Kaufland\Model\Cron\AbstractTask
{
    public const NICK = 'order/create_failed';

    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    /** @var \M2E\Kaufland\Model\Cron\Task\Order\CreatorFactory */
    private CreatorFactory $orderCreatorFactory;
    private \M2E\Kaufland\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\Kaufland\Model\Order\Repository $orderRepository,
        \M2E\Kaufland\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory,
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Cron\Manager $cronManager,
        \M2E\Kaufland\Model\Synchronization\LogService $syncLogger,
        \M2E\Core\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $modelFactory,
            $activeRecordFactory,
            $taskRepo,
            $resource
        );
        $this->orderRepository = $orderRepository;
        $this->accountRepository = $accountRepository;
        $this->orderCreatorFactory = $orderCreatorFactory;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function getSynchronizationLog(): \M2E\Kaufland\Model\Synchronization\LogService
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setTask(\M2E\Kaufland\Model\Synchronization\Log::TASK_ORDERS);

        return $synchronizationLog;
    }

    protected function performActions()
    {
        foreach ($this->accountRepository->getAll() as $account) {
            try {
                $borderDate = \M2E\Core\Helper\Date::createCurrentGmt();
                $borderDate->modify('-15 minutes');

                $kauflandOrders = $this->orderRepository->findForAttemptMagentoCreate(
                    $account,
                    $borderDate,
                    \M2E\Kaufland\Model\Order::MAGENTO_ORDER_CREATE_MAX_TRIES
                );
                $this->createMagentoOrders($kauflandOrders);
            } catch (\Exception $exception) {
                $message = (string)\__(
                    'The "Create Failed Orders" Action for Account "%1" was completed with error.',
                    $account->getTitle(),
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    protected function createMagentoOrders($kauflandOrders)
    {
        $ordersCreator = $this->orderCreatorFactory->create($this->getSynchronizationLog());

        foreach ($kauflandOrders as $order) {
            /** @var \M2E\Kaufland\Model\Order $order */

            if ($ordersCreator->isOrderChangedInParallelProcess($order)) {
                continue;
            }

            if (!$order->canCreateMagentoOrder()) {
                $order->addData([
                    \M2E\Kaufland\Model\ResourceModel\Order::COLUMN_MAGENTO_ORDER_CREATION_FAILURE => \M2E\Kaufland\Model\Order::MAGENTO_ORDER_CREATION_FAILED_NO,
                    \M2E\Kaufland\Model\ResourceModel\Order::COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT => 0,
                    \M2E\Kaufland\Model\ResourceModel\Order::COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE => null,
                ]);
                $this->orderRepository->save($order);
                continue;
            }

            $ordersCreator->createMagentoOrder($order);
        }
    }
}
