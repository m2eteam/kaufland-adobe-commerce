<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task\Order;

class UploadByUser extends \M2E\Kaufland\Model\Cron\AbstractTask
{
    public const NICK = 'order/upload_by_user';

    private \M2E\Kaufland\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory;
    private \M2E\Kaufland\Model\Cron\Task\Order\UploadByUser\ManagerFactory $uploadByUserManagerFactory;
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\Kaufland\Connector\Order\Receive\ItemsByCreateDate\Processor $receiveOrderProcessor;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Kaufland\Connector\Order\Receive\ItemsByCreateDate\Processor $receiveOrderProcessor,
        \M2E\Kaufland\Model\Cron\Task\Order\UploadByUser\ManagerFactory $uploadByUserManagerFactory,
        \M2E\Kaufland\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory,
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
            $resource,
        );
        $this->orderCreatorFactory = $orderCreatorFactory;
        $this->uploadByUserManagerFactory = $uploadByUserManagerFactory;
        $this->accountRepository = $accountRepository;
        $this->receiveOrderProcessor = $receiveOrderProcessor;
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

    protected function performActions(): void
    {
        $ordersCreator = $this->orderCreatorFactory->create($this->getSynchronizationLog());
        $ordersCreator->setValidateAccountCreateDate(false);

        foreach ($this->accountRepository->getAll() as $account) {
            $manager = $this->uploadByUserManagerFactory->create($account);
            if (!$manager->isEnabled()) {
                continue;
            }

            try {
                $toTime = $manager->getToDate() ?? \M2E\Core\Helper\Date::createCurrentGmt();
                $fromTime = $manager->getCurrentFromDate() ?? $manager->getFromDate();

                $response = $this->receiveOrderProcessor->process(
                    $account,
                    $fromTime,
                    $toTime
                );

                $this->processResponseMessages($response->getMessageCollection());

                $responseMaxDate = clone $response->getToDate();

                $this->updateUploadRecord($manager, $responseMaxDate);

                if (empty($response->getOrders())) {
                    continue;
                }

                $processKauflandOrders = $ordersCreator
                    ->processKauflandOrders($account, $response->getOrders());

                $ordersCreator->processMagentoOrders($processKauflandOrders);
            } catch (\Throwable $exception) {
                $message = (string)__(
                    'The "Upload Orders By User" Action for Kaufland Account "%account" was completed with error.',
                    ['account' => $account->getTitle()],
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    private function processResponseMessages(
        \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection
    ): void {
        foreach ($messageCollection->getMessages() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError()
                ? \M2E\Kaufland\Model\Log\AbstractModel::TYPE_ERROR
                : \M2E\Kaufland\Model\Log\AbstractModel::TYPE_WARNING;

            $this
                ->getSynchronizationLog()
                ->add((string)\__($message->getText()), $logType);
        }
    }

    private function updateUploadRecord(UploadByUser\Manager $manager, \DateTime $responseMaxDate): void
    {
        $manager->setCurrentFromDate($responseMaxDate->format('Y-m-d H:i:s'));

        if ($manager->getCurrentFromDate()->getTimestamp() >= $manager->getToDate()->getTimestamp()) {
            $manager->clear();
        }
    }
}
