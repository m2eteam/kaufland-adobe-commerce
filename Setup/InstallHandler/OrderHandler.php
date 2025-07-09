<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\InstallHandler;

use M2E\Core\Model\ResourceModel\Setup;
use M2E\Kaufland\Helper\Module\Database\Tables as TablesHelper;
use Magento\Framework\DB\Ddl\Table;
use M2E\Kaufland\Model\ResourceModel\Order as OrderResource;
use M2E\Kaufland\Model\ResourceModel\Order\Note as OrderNoteResource;

class OrderHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Kaufland\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installOrderTable($setup);
        $this->installOrderItemTable($setup);
        $this->installOrderNoteTable($setup);
        $this->installOrderChangeTable($setup);
    }

    private function installOrderTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_ORDER);

        $orderTable = $setup->getConnection()->newTable($tableName);

        $orderTable
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                'storefront_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILURE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderResource::COLUMN_RESERVATION_STATE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'reservation_start_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'additional_data',
                Table::TYPE_TEXT
            )
            ->addColumn(
                'kaufland_order_id',
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'order_status',
                Table::TYPE_TEXT,
                30
            )
            ->addColumn(
                'purchase_create_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'purchase_update_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'paid_amount',
                Table::TYPE_DECIMAL,
                [12, 4],
                [
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'currency',
                Table::TYPE_TEXT,
                10,
                [
                    'nullable' => false,
                ]
            )
            ->addColumn(
                'tax_details',
                Table::TYPE_TEXT
            )
            ->addColumn(
                'buyer_user_id',
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'buyer_email',
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderResource::COLUMN_SHIPPING_DETAILS,
                Table::TYPE_TEXT
            )
            ->addColumn(
                'tracking_details',
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                'delivery_time_expires_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderResource::COLUMN_BILLING_DETAILS,
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addIndex('kaufland_order_id', 'kaufland_order_id')
            ->addIndex('buyer_email', 'buyer_email')
            ->addIndex('buyer_user_id', 'buyer_user_id')
            ->addIndex('paid_amount', 'paid_amount')
            ->addIndex('delivery_time_expires_date', 'delivery_time_expires_date')
            ->addIndex('account_id', OrderResource::COLUMN_ACCOUNT_ID)
            ->addIndex('magento_order_id', OrderResource::COLUMN_MAGENTO_ORDER_ID)
            ->addIndex('magento_order_creation_failure', OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILURE)
            ->addIndex('magento_order_creation_fails_count', OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT)
            ->addIndex(
                'magento_order_creation_latest_attempt_date',
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE
            )
            ->addIndex('storefront_id', 'storefront_id')
            ->addIndex('reservation_state', OrderResource::COLUMN_RESERVATION_STATE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($orderTable);
    }

    private function installOrderItemTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_ITEM);

        $orderItemTable = $setup->getConnection()->newTable($tableName);

        $orderItemTable
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                'product_details',
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                'qty_reserved',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => 0]
            )
            ->addColumn(
                'additional_data',
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                'kaufland_order_item_id',
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'kaufland_product_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'kaufland_offer_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'eans',
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                'qty_purchased',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'sale_price',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                'revenue_gross',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                'revenue_net',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                'tax_details',
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                'status',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('kaufland_order_item_id', 'kaufland_order_item_id')
            ->addIndex('kaufland_product_id', 'kaufland_product_id')
            ->addIndex('kaufland_offer_id', 'kaufland_offer_id')
            ->addIndex('eans', 'eans')
            ->addIndex('title', 'title')
            ->addIndex('order_id', 'order_id')
            ->addIndex('product_id', 'product_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($orderItemTable);
    }

    private function installOrderNoteTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_NOTE);

        $orderNoteTable = $setup->getConnection()->newTable($tableName);

        $orderNoteTable
            ->addColumn(
                OrderNoteResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                OrderNoteResource::COLUMN_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderNoteResource::COLUMN_NOTE,
                Table::TYPE_TEXT,
            )
            ->addColumn(
                OrderNoteResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderNoteResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
            )
            ->addIndex('order_id', OrderNoteResource::COLUMN_ORDER_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($orderNoteTable);
    }

    private function installOrderChangeTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_CHANGE);

        $orderChangeTable = $setup->getConnection()->newTable($tableName);

        $orderChangeTable
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                'action',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                'params',
                Table::TYPE_TEXT
            )
            ->addColumn(
                'creator_type',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'processing_attempt_count',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'processing_attempt_date',
                Table::TYPE_DATETIME,
            )
            ->addColumn(
                'hash',
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME
            )
            ->addIndex('action', 'action')
            ->addIndex('creator_type', 'creator_type')
            ->addIndex('hash', 'hash')
            ->addIndex('order_id', 'order_id')
            ->addIndex('processing_attempt_count', 'processing_attempt_count')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($orderChangeTable);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }
}
