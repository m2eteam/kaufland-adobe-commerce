<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order;

use M2E\Kaufland\Model\ResourceModel\Order as OrderResource;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory;
    private OrderResource $orderResource;
    private \M2E\Kaufland\Model\OrderFactory $orderFactory;
    private \M2E\Kaufland\Model\ResourceModel\Order\Item $orderItemResource;
    /** @var \M2E\Kaufland\Model\Order\ItemFactory */
    private ItemFactory $itemFactory;

    public function __construct(
        OrderResource $orderResource,
        \M2E\Kaufland\Model\OrderFactory $orderFactory,
        \M2E\Kaufland\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Order\Item $orderItemResource,
        \M2E\Kaufland\Model\Order\ItemFactory $itemFactory,
        \M2E\Kaufland\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->orderChangeCollectionFactory = $orderChangeCollectionFactory;
        $this->orderNoteCollectionFactory = $orderNoteCollectionFactory;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->orderItemResource = $orderItemResource;
        $this->itemFactory = $itemFactory;
    }

    public function get(int $id): \M2E\Kaufland\Model\Order
    {
        $order = $this->find($id);
        if ($order === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic("Order $id not found.");
        }

        return $order;
    }

    public function find(int $id): ?\M2E\Kaufland\Model\Order
    {
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $id);

        if ($order->isObjectNew()) {
            return null;
        }

        return $order;
    }

    public function findByMagentoOrderId(int $id): ?\M2E\Kaufland\Model\Order
    {
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $id, OrderResource::COLUMN_MAGENTO_ORDER_ID);

        if ($order->isObjectNew()) {
            return null;
        }

        return $order;
    }

    public function removeByAccountId(int $accountId): void
    {
        $this->removeRelatedOrderChangesByAccountId($accountId);
        $this->removeRelatedOrderItemsByAccountId($accountId);
        $this->removeRelatedOrderNoteByAccountId($accountId);

        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->getConnection()->delete(
            $orderCollection->getMainTable(),
            ['account_id = ?' => $accountId]
        );
    }

    public function save(\M2E\Kaufland\Model\Order $order): void
    {
        $this->orderResource->save($order);
    }

    private function removeRelatedOrderItemsByAccountId(int $accountId): void
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            OrderResource::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $orderCollection->getSelect()
                        ->reset('columns')
                        ->columns('id');

        $orderItemCollection = $this->orderItemCollectionFactory->create();
        $orderItemCollection->getConnection()->delete(
            $orderItemCollection->getMainTable(),
            [
                \M2E\Kaufland\Model\ResourceModel\Order\Item::COLUMN_ORDER_ID . ' IN (?)'
                => $orderCollection->getSelect(),
            ]
        );
    }

    private function removeRelatedOrderChangesByAccountId(int $accountId): void
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            OrderResource::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $orderCollection->getSelect()
                        ->reset('columns')
                        ->columns('id');

        $orderChangeCollection = $this->orderChangeCollectionFactory->create();
        $orderChangeCollection->getConnection()->delete(
            $orderChangeCollection->getMainTable(),
            [
                \M2E\Kaufland\Model\ResourceModel\Order\Change::COLUMN_ORDER_ID . ' IN (?)'
                => $orderCollection->getSelect(),
            ]
        );
    }

    private function removeRelatedOrderNoteByAccountId(int $accountId): void
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            OrderResource::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $orderCollection->getSelect()
                        ->reset('columns')
                        ->columns('id');

        $orderNoteCollection = $this->orderNoteCollectionFactory->create();
        $orderNoteCollection->getConnection()->delete(
            $orderNoteCollection->getMainTable(),
            [
                \M2E\Kaufland\Model\ResourceModel\Order\Note::COLUMN_ORDER_ID . ' IN (?)'
                => $orderCollection->getSelect(),
            ]
        );
    }

    // ----------------------------------------

    /**
     * @param \M2E\Kaufland\Model\Order $order
     *
     * @return \M2E\Kaufland\Model\Order\Item[]
     */
    public function findItemsByOrder(\M2E\Kaufland\Model\Order $order): array
    {
        $collection = $this->orderItemCollectionFactory->create();
        $collection
            ->addFieldToFilter(\M2E\Kaufland\Model\ResourceModel\Order\Item::COLUMN_ORDER_ID, (int)$order->getId());

        $result = [];
        foreach ($collection->getItems() as $item) {
            $result[] = $item->setOrder($order);
        }

        return $result;
    }

    public function findItemById(int $id): ?\M2E\Kaufland\Model\Order\Item
    {
        $orderItem = $this->itemFactory->create();
        $this->orderItemResource->load($orderItem, $id);

        if ($orderItem->isObjectNew()) {
            return null;
        }

        return $orderItem;
    }

    /**
     * @param \M2E\Kaufland\Model\Account $account
     *
     * @return \M2E\Kaufland\Model\Order[]
     * @throws \Exception
     */
    public function findForReleaseReservation(\M2E\Kaufland\Model\Account $account): array
    {
        $collection = $this->orderCollectionFactory->create()
                                                   ->addFieldToFilter(OrderResource::COLUMN_ACCOUNT_ID, $account->getId())
                                                   ->addFieldToFilter(
                                                       OrderResource::COLUMN_RESERVATION_STATE,
                                                       \M2E\Kaufland\Model\Order\Reserve::STATE_PLACED
                                                   );

        $reservationDays = $account->getOrdersSettings()->getQtyReservationDays();

        $minReservationStartDate = \M2E\Core\Helper\Date::createCurrentGmt();
        $minReservationStartDate->modify('- ' . $reservationDays . ' days');
        $minReservationStartDate = $minReservationStartDate->format('Y-m-d H:i');

        $collection->addFieldToFilter('reservation_start_date', ['lteq' => $minReservationStartDate]);

        return $collection->getItems();
    }

    /**
     * @param array $ids
     *
     * @return \M2E\Kaufland\Model\Order[]
     */
    public function findOrdersForReservationCancel(array $ids): array
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(OrderResource::COLUMN_ID, ['in' => $ids]);
        $orderCollection->addFieldToFilter(
            OrderResource::COLUMN_RESERVATION_STATE,
            \M2E\Kaufland\Model\Order\Reserve::STATE_PLACED
        );

        return array_values($orderCollection->getItems());
    }

    /**
     * @param array $ids
     *
     * @return \M2E\Kaufland\Model\Order[]
     */
    public function findOrdersForReservationPlace(array $ids): array
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(OrderResource::COLUMN_ID, ['in' => $ids]);
        $orderCollection->addFieldToFilter(
            OrderResource::COLUMN_RESERVATION_STATE,
            ['neq' => \M2E\Kaufland\Model\Order\Reserve::STATE_PLACED]
        );
        $orderCollection->addFieldToFilter(
            OrderResource::COLUMN_MAGENTO_ORDER_ID,
            ['null' => true]
        );

        return array_values($orderCollection->getItems());
    }

    public function findForAttemptMagentoCreate(
        \M2E\Kaufland\Model\Account $account,
        \DateTime $borderDate,
        int $creationAttemptsLessThan
    ): array {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter(OrderResource::COLUMN_ACCOUNT_ID, $account->getId());
        $collection->addFieldToFilter(OrderResource::COLUMN_MAGENTO_ORDER_ID, ['null' => true]);
        $collection->addFieldToFilter(
            OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILURE,
            \M2E\Kaufland\Model\Order::MAGENTO_ORDER_CREATION_FAILED_YES,
        );
        $collection->addFieldToFilter(
            OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT,
            ['lt' => $creationAttemptsLessThan],
        );
        $collection->addFieldToFilter(
            OrderResource::COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE,
            ['lt' => $borderDate->format('Y-m-d H:i:s')],
        );
        $collection->getSelect()->order(
            OrderResource::COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE . ' ASC'
        );
        $collection->setPageSize(25);

        return $collection->getItems();
    }

    public function getUnshippedCountForRange(
        \DateTimeInterface $from,
        \DateTimeInterface $to
    ): int {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter(OrderResource::COLUMN_DELIVERY_TIME_EXPIRES_DATE, ['notnull' => true]);
        $collection->addFieldToFilter(OrderResource::COLUMN_DELIVERY_TIME_EXPIRES_DATE, [
            'from' => $from->format('Y-m-d H:i:s'),
            'to'   => $to->format('Y-m-d H:i:s'),
        ]);

        $collection->addFieldToFilter(
            OrderResource::COLUMN_ORDER_STATUS,
            \M2E\Kaufland\Model\Order::STATUS_UNSHIPPED
        );

        return (int)$collection->getSize();
    }

    public function getLateUnshippedCount(): int
    {
        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();

        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter(OrderResource::COLUMN_DELIVERY_TIME_EXPIRES_DATE, ['notnull' => true]);
        $collection->addFieldToFilter(
            OrderResource::COLUMN_DELIVERY_TIME_EXPIRES_DATE,
            ['lt' => $currentDate->format('Y-m-d H:i:s')]
        );

        $collection->addFieldToFilter(
            OrderResource::COLUMN_ORDER_STATUS,
            \M2E\Kaufland\Model\Order::STATUS_UNSHIPPED
        );

        return (int)$collection->getSize();
    }

    public function getUnshippedCountFrom(\DateTimeInterface $from): int
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter(OrderResource::COLUMN_DELIVERY_TIME_EXPIRES_DATE, ['notnull' => true]);
        $collection->addFieldToFilter(
            OrderResource::COLUMN_DELIVERY_TIME_EXPIRES_DATE,
            ['gteq' => $from->format('Y-m-d H:i:s')]
        );

        $collection->addFieldToFilter(
            OrderResource::COLUMN_ORDER_STATUS,
            \M2E\Kaufland\Model\Order::STATUS_UNSHIPPED
        );

        return (int)$collection->getSize();
    }

    /**
     * @return \M2E\Core\Model\Dashboard\Sales\Point[]
     */
    public function getAmountPoints(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        bool $isHourlyInterval
    ): array {
        return $this->getPoints(
            'SUM(paid_amount)',
            $from,
            $to,
            $isHourlyInterval
        );
    }

    /**
     * @return \M2E\Core\Model\Dashboard\Sales\Point[]
     */
    public function getQuantityPoints(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        bool $isHourlyInterval
    ): array {
        return $this->getPoints('COUNT(*)', $from, $to, $isHourlyInterval);
    }

    /**
     * @return \M2E\Core\Model\Dashboard\Sales\Point[]
     */
    private function getPoints(
        string $valueColumn,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        bool $isHourlyInterval
    ): array {
        $collection = $this->orderCollectionFactory->create();

        $collection->addFieldToFilter(OrderResource::COLUMN_ORDER_STATUS, ['in' => [
            \M2E\Kaufland\Model\Order::STATUS_UNSHIPPED,
            \M2E\Kaufland\Model\Order::STATUS_SHIPPED,
            \M2E\Kaufland\Model\Order::STATUS_SHIPPED_PARTIALLY
        ]]);
        $collection->addFieldToFilter(OrderResource::COLUMN_PURCHASE_CREATE_DATE, [
            'from' => $from->format('Y-m-d H:i:s'),
            'to'   => $to->format('Y-m-d H:i:s')
        ]);

        $select = $collection->getSelect();
        $select->reset('columns');
        $select->columns(
            [
                sprintf(
                    'DATE_FORMAT(%s, "%s") AS date',
                    OrderResource::COLUMN_PURCHASE_CREATE_DATE,
                    $isHourlyInterval ? '%Y-%m-%d %H' : '%Y-%m-%d'
                ),
                sprintf('%s AS value', $valueColumn),
            ]
        );

        if ($isHourlyInterval) {
            $select->group(sprintf('HOUR(main_table.%s)', OrderResource::COLUMN_PURCHASE_CREATE_DATE));
        }
        $select->group(sprintf('DAY(main_table.%s)', OrderResource::COLUMN_PURCHASE_CREATE_DATE));
        $select->order('date');

        $queryData = $select->query()->fetchAll();

        $keyValueData = array_combine(
            array_column($queryData, 'date'),
            array_column($queryData, 'value')
        );

        return $this->makePoint($keyValueData, $from, $to, $isHourlyInterval);
    }

    /**
     * @return \M2E\Core\Model\Dashboard\Sales\Point[]
     */
    private function makePoint(
        array $data,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        bool $isHourlyInterval
    ): array {
        $intervalFormat = $isHourlyInterval ? 'PT1H' : 'P1D';
        $dateFormat = $isHourlyInterval ? 'Y-m-d H' : 'Y-m-d';

        $period = new \DatePeriod(
            $from,
            new \DateInterval($intervalFormat),
            $to
        );

        $points = [];
        foreach ($period as $value) {
            $pointValue = (float)($data[$value->format($dateFormat)] ?? 0);
            $pointDate = clone $value;
            $points[] = new \M2E\Core\Model\Dashboard\Sales\Point(
                $pointValue,
                $pointDate
            );
        }

        return $points;
    }
}
