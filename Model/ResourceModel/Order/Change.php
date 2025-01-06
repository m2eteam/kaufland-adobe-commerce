<?php

namespace M2E\Kaufland\Model\ResourceModel\Order;

class Change extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ORDER_ID = 'order_id';
    public const COLUMN_ACTION = 'action';
    public const COLUMN_PARAMS = 'params';
    public const COLUMN_CREATOR_TYPE = 'creator_type';
    public const COLUMN_PROCESSING_ATTEMPT_COUNT = 'processing_attempt_count';
    public const COLUMN_PROCESSING_ATTEMPT_DATE = 'processing_attempt_date';
    public const COLUMN_HASH = 'hash';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init(\M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_ORDER_CHANGE, 'id');
    }

    public function deleteByIds(array $ids)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            [
                'id IN(?)' => $ids,
            ]
        );
    }

    public function deleteByOrderAction($orderId, $action)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            [
                'order_id = ?' => $orderId,
                'action = ?' => $action,
            ]
        );
    }

    public function deleteByProcessingAttemptCount($count = 3)
    {
        $count = (int)$count;

        if ($count <= 0) {
            return;
        }

        $where = [
            'processing_attempt_count >= ?' => $count,
        ];

        $this->getConnection()->delete(
            $this->getMainTable(),
            $where
        );
    }
}
