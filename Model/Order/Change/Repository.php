<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order\Change;

use M2E\Kaufland\Model\ResourceModel\Order\Change as ChangeResource;

class Repository
{
    private ChangeResource $changeResource;
    private \M2E\Kaufland\Model\ResourceModel\Order\Change\CollectionFactory $collectionFactory;
    private \M2E\Kaufland\Model\Order\ChangeFactory $changeFactory;
    private \M2E\Kaufland\Model\ResourceModel\Order $orderResource;

    public function __construct(
        ChangeResource $changeResource,
        \M2E\Kaufland\Model\ResourceModel\Order\Change\CollectionFactory $collectionFactory,
        \M2E\Kaufland\Model\Order\ChangeFactory $changeFactory,
        \M2E\Kaufland\Model\ResourceModel\Order $orderResource
    ) {
        $this->changeResource = $changeResource;
        $this->collectionFactory = $collectionFactory;
        $this->changeFactory = $changeFactory;
        $this->orderResource = $orderResource;
    }

    public function create(\M2E\Kaufland\Model\Order\Change $change): void
    {
        $this->changeResource->save($change);
    }

    public function save(\M2E\Kaufland\Model\Order\Change $change): void
    {
        $this->changeResource->save($change);
    }

    public function delete(\M2E\Kaufland\Model\Order\Change $change): void
    {
        $this->changeResource->delete($change);
    }

    public function findExist(int $orderId, string $action, string $hash): ?\M2E\Kaufland\Model\Order\Change
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ChangeResource::COLUMN_ORDER_ID, $orderId)
                   ->addFieldToFilter(ChangeResource::COLUMN_ACTION, $action)
                   ->addFieldToFilter(ChangeResource::COLUMN_HASH, $hash)
                   ->setPageSize(1);

        /** @var \M2E\Kaufland\Model\Order\Change $change */
        $change = $collection->getFirstItem();
        if ($change->isObjectNew()) {
            return null;
        }

        return $change;
    }

    public function createShippingOrUpdateNotProcessed(
        \M2E\Kaufland\Model\Order $order,
        array $items,
        ?int $initiator
    ): void {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ChangeResource::COLUMN_ORDER_ID, $order->getId())
                   ->addFieldToFilter(
                       ChangeResource::COLUMN_ACTION,
                       \M2E\Kaufland\Model\Order\Change::ACTION_UPDATE_SHIPPING,
                   )
                   ->addFieldToFilter(ChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT, 0);

        $change = $collection->getFirstItem();
        if ($change->isObjectNew()) {
            $change = $this->changeFactory->create();
            $change->init(
                $order->getId(),
                \M2E\Kaufland\Model\Order\Change::ACTION_UPDATE_SHIPPING,
                $initiator,
                $params = [
                    'items' => $items,
                ],
                self::generateHash($order->getId(), \M2E\Kaufland\Model\Order\Change::ACTION_UPDATE_SHIPPING, $params),
            );

            $this->changeResource->save($change);

            return;
        }

        $params = $change->getParams();
        $existItems = $this->groupItemsById($params['items'] ?? []);
        foreach ($items as $itemData) {
            $existItems[$itemData['item_id']] = $itemData;
        }

        $params['items'] = array_values($existItems);

        $change->setParams($params);

        $this->changeResource->save($change);
    }

    /**
     * @param \M2E\Kaufland\Model\Account $account
     * @param int $limit
     *
     * @return \M2E\Kaufland\Model\Order\Change[]
     */
    public function findCanceledReadyForProcess(\M2E\Kaufland\Model\Account $account, int $limit): array
    {
        $collection = $this->collectionFactory->create();
        $collection->joinInner(
            ['orders' => $this->orderResource->getMainTable()],
            sprintf(
                '`orders`.`%s` = `main_table`.`%s`',
                \M2E\Kaufland\Model\ResourceModel\Order::COLUMN_ID,
                \M2E\Kaufland\Model\ResourceModel\Order\Change::COLUMN_ORDER_ID,
            ),
            [
                'account_id' => \M2E\Kaufland\Model\ResourceModel\Order::COLUMN_ACCOUNT_ID,
            ],
        );
        $collection->addFieldToFilter(
            sprintf('orders.%s', \M2E\Kaufland\Model\ResourceModel\Order::COLUMN_ACCOUNT_ID),
            ['eq' => $account->getId()],
        );

        $hourAgo = \M2E\Core\Helper\Date::createCurrentGmt();
        $hourAgo->modify('-1 hour');

        $collection->addFieldToFilter(
            [
                \M2E\Kaufland\Model\ResourceModel\Order\Change::COLUMN_PROCESSING_ATTEMPT_DATE,
                \M2E\Kaufland\Model\ResourceModel\Order\Change::COLUMN_PROCESSING_ATTEMPT_DATE,
            ],
            [
                ['null' => true],
                ['lteq' => $hourAgo->format('Y-m-d H:i:s')],
            ],
        );

        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Order\Change::COLUMN_ACTION,
            ['eq' => \M2E\Kaufland\Model\Order\Change::ACTION_CANCEL],
        );

        $collection->setPageSize($limit);
        $collection->getSelect()->group(['main_table.id']);

        return array_values($collection->getItems());
    }

    private function groupItemsById(array $items): array
    {
        $result = [];
        foreach ($items as $itemData) {
            $result[$itemData['item_id']] = $itemData;
        }

        return $result;
    }

    // ----------------------------------------

    /**
     * @param \M2E\Kaufland\Model\Account $account
     * @param int $limit
     *
     * @return \M2E\Kaufland\Model\Order\Change[]
     */
    public function findShippingForProcess(\M2E\Kaufland\Model\Account $account, int $limit): array
    {
        $collection = $this->collectionFactory->create();

        $orderTable = $this->orderResource->getMainTable();

        $collection->getSelect()->join(
            ['mo' => $orderTable],
            sprintf(
                '(`mo`.`id` = `main_table`.`%s` AND `mo`.`%s` = %s)',
                ChangeResource::COLUMN_ORDER_ID,
                \M2E\Kaufland\Model\ResourceModel\Order::COLUMN_ACCOUNT_ID,
                $account->getId(),
            ),
            ['account_id'],
        );

        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();
        $currentDate->modify("-3600 seconds");

        $collection->getSelect()
                   ->where(
                       sprintf(
                           '%s = 0 OR %s <= ?',
                           ChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT,
                           ChangeResource::COLUMN_PROCESSING_ATTEMPT_DATE,
                       ),
                       $currentDate->format('Y-m-d H:i:s'),
                   );
        $collection->addFieldToFilter(
            ChangeResource::COLUMN_ACTION,
            ['eq' => \M2E\Kaufland\Model\Order\Change::ACTION_UPDATE_SHIPPING],
        );
        $collection->setPageSize($limit);
        $collection->getSelect()->group([ChangeResource::COLUMN_ORDER_ID]);

        return array_values($collection->getItems());
    }

    /**
     * @param \M2E\Kaufland\Model\Account $account
     * @param int $limit
     *
     * @return \M2E\Kaufland\Model\Order\Change[]
     */
    public function findSendInvoiceForProcess(\M2E\Kaufland\Model\Account $account, int $limit): array
    {
        $collection = $this->collectionFactory->create();
        $collection->joinInner(
            ['orders' => $this->orderResource->getMainTable()],
            sprintf(
                '`orders`.`%s` = `main_table`.`%s`',
                \M2E\Kaufland\Model\ResourceModel\Order::COLUMN_ID,
                \M2E\Kaufland\Model\ResourceModel\Order\Change::COLUMN_ORDER_ID,
            ),
            [
                'account_id' => \M2E\Kaufland\Model\ResourceModel\Order::COLUMN_ACCOUNT_ID,
            ],
        );
        $collection->addFieldToFilter(
            sprintf('orders.%s', \M2E\Kaufland\Model\ResourceModel\Order::COLUMN_ACCOUNT_ID),
            ['eq' => $account->getId()],
        );

        $hourAgo = \M2E\Core\Helper\Date::createCurrentGmt();
        $hourAgo->modify('-1 hour');

        $collection->addFieldToFilter(
            [
                \M2E\Kaufland\Model\ResourceModel\Order\Change::COLUMN_PROCESSING_ATTEMPT_DATE,
                \M2E\Kaufland\Model\ResourceModel\Order\Change::COLUMN_PROCESSING_ATTEMPT_DATE,
            ],
            [
                ['null' => true],
                ['lteq' => $hourAgo->format('Y-m-d H:i:s')],
            ],
        );

        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Order\Change::COLUMN_ACTION,
            ['eq' => \M2E\Kaufland\Model\Order\Change::ACTION_SEND_INVOICE],
        );

        $collection->setPageSize($limit);
        $collection->getSelect()->group(['main_table.id']);

        return array_values($collection->getItems());
    }

    public function remove(\M2E\Kaufland\Model\Order\Change $change): void
    {
        $this->changeResource->delete($change);
    }

    public function incrementAttemptCount(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $this->changeResource->getConnection()
                             ->update(
                                 $this->changeResource->getMainTable(),
                                 [
                                     ChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT => new \Zend_Db_Expr(
                                         'processing_attempt_count + 1',
                                     ),
                                     ChangeResource::COLUMN_PROCESSING_ATTEMPT_DATE => \M2E\Core\Helper\Date::createCurrentGmt(
                                     )->format('Y-m-d H:i:s'),
                                 ],
                                 [
                                     'id IN (?)' => $ids,
                                 ],
                             );
    }

    public function deleteByProcessingAttemptCount(int $count): void
    {
        $where = [
            sprintf('%s >= ?', ChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT) => $count,
        ];

        $this->changeResource
            ->getConnection()->delete(
                $this->changeResource->getMainTable(),
                $where,
            );
    }

    private static function generateHash($orderId, $action, array $params): string
    {
        return sha1($orderId . '-' . $action . '-' . json_encode($params, JSON_THROW_ON_ERROR));
    }
}
