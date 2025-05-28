<?php

namespace M2E\Kaufland\Model\Cron\Task\Order;

class Creator
{
    private bool $isValidateByAccountCreateDate = true;
    private \M2E\Kaufland\Model\Synchronization\LogService $syncLogService;
    private \M2E\Kaufland\Model\Order\BuilderFactory $orderBuilderFactory;
    private \M2E\Kaufland\Helper\Module\Exception $exceptionHelper;
    private \M2E\Kaufland\Model\Order\Repository $orderRepository;
    private \M2E\Kaufland\Model\Order\CreditMemoCreate $createCreditMemo;

    public function __construct(
        \M2E\Kaufland\Model\Synchronization\LogService $syncLogService,
        \M2E\Kaufland\Model\Order\BuilderFactory $orderBuilderFactory,
        \M2E\Kaufland\Helper\Module\Exception $exceptionHelper,
        \M2E\Kaufland\Model\Order\Repository $orderRepository,
        \M2E\Kaufland\Model\Order\CreditMemoCreate $createCreditMemo
    ) {
        $this->syncLogService = $syncLogService;
        $this->orderBuilderFactory = $orderBuilderFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->orderRepository = $orderRepository;
        $this->createCreditMemo = $createCreditMemo;
    }

    public function setValidateAccountCreateDate(bool $mode): void
    {
        $this->isValidateByAccountCreateDate = $mode;
    }

    /**
     * @param \M2E\Kaufland\Model\Account $account
     * @param array $ordersData
     *
     * @return \M2E\Kaufland\Model\Order[]
     * @throws \DateMalformedStringException
     */
    public function processKauflandOrders(
        \M2E\Kaufland\Model\Account $account,
        array $ordersData
    ): array {
        $orders = [];

        $accountCreateDate = clone $account->getCreateDate();
        $boundaryCreationDate = \M2E\Core\Helper\Date::createCurrentGmt()->modify('-90 days');

        foreach ($ordersData as $kauflandOrderData) {
            try {
                $orderCreateDate = \M2E\Core\Helper\Date::createDateGmt($kauflandOrderData['create_date']);

                if (
                    !$this->isValidOrderByAccountCreateData($accountCreateDate, $boundaryCreationDate, $orderCreateDate)
                ) {
                    continue;
                }

                if (!$this->isValidOrderByStorefront($account, $kauflandOrderData)) {
                    continue;
                }

                $orderBuilder = $this->orderBuilderFactory->create();
                $orderBuilder->initialize($account, $kauflandOrderData);

                $order = $orderBuilder->process();

                if ($order) {
                    $orders[] = $order;
                }
            } catch (\Throwable $exception) {
                $this->syncLogService->addFromException($exception);
                $this->exceptionHelper->process($exception);
                continue;
            }
        }

        return array_filter($orders);
    }

    /**
     * @param \M2E\Kaufland\Model\Order[] $orders
     */
    public function processMagentoOrders(array $orders): void
    {
        foreach ($orders as $order) {
            if ($this->isOrderChangedInParallelProcess($order)) {
                continue;
            }

            try {
                $this->createMagentoOrder($order);
            } catch (\Throwable $exception) {
                $this->syncLogService->addFromException($exception);
                $this->exceptionHelper->process($exception);

                continue;
            }
        }
    }

    public function createMagentoOrder(\M2E\Kaufland\Model\Order $order)
    {
        if ($order->canCreateMagentoOrder()) {
            try {
                $order->getLogService()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_EXTENSION);

                $order->addInfoLog(
                    strtr(
                        'Magento order creation rules are met. :extension_title will attempt to create Magento order.',
                        [
                            ':extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
                        ]
                    ),
                    [],
                    [],
                    true
                );

                $order->createMagentoOrder();
            } catch (\Throwable $exception) {
                return;
            }
        }

        if ($order->getReserve()->isNotProcessed() && $order->isReservable()) {
            $order->getReserve()->place();
        }

        if ($order->canCreateInvoice()) {
            $order->createInvoice();
        }

        $order->createShipments();

        if (!$order->getAccount()->getOrdersSettings()->isOrderStatusMappingModeDefault()) {
            $order->updateMagentoOrderStatus();
        }

        if ($order->canCreateTracks()) {
            $order->createTracks();
        }

        $this->createCreditMemo->process($order);
    }

    /**
     * This is going to protect from Magento Orders duplicates.
     * (Is assuming that there may be a parallel process that has already created Magento Order)
     * But this protection is not covering cases when two parallel cron processes are isolated by mysql transactions
     */
    public function isOrderChangedInParallelProcess(\M2E\Kaufland\Model\Order $order): bool
    {
        $dbOrder = $this->orderRepository->find($order->getId());
        if ($dbOrder === null) {
            return false;
        }

        if ($dbOrder->getMagentoOrderId() != $order->getMagentoOrderId()) {
            return true;
        }

        return false;
    }

    private function isValidOrderByAccountCreateData(
        \DateTime $accountCreateDate,
        \DateTime $boundaryCreationDate,
        \DateTime $orderCreateDate
    ): bool {
        if (!$this->isValidateByAccountCreateDate) {
            return true;
        }

        if ($orderCreateDate >= $accountCreateDate) {
            return true;
        }

        return $orderCreateDate >= $boundaryCreationDate;
    }

    /**
     * @param \M2E\Kaufland\Model\Account $account
     * @param array $data
     *
     * @return bool
     */
    private function isValidOrderByStorefront(
        \M2E\Kaufland\Model\Account $account,
        array $data
    ): bool {
        if ($account->findStorefrontByCode($data['storefront_code']) !== null) {
            return true;
        }

        return false;
    }
}
