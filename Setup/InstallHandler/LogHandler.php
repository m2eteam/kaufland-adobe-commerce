<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\InstallHandler;

use M2E\Kaufland\Helper\Module\Database\Tables as TablesHelper;
use M2E\Kaufland\Model\ResourceModel\Listing\Log as ListingLogResource;
use M2E\Kaufland\Model\ResourceModel\Log\System as LogSystemResource;
use M2E\Kaufland\Model\ResourceModel\Synchronization\Log as SyncLogResource;
use Magento\Framework\DB\Ddl\Table;
use M2E\Core\Model\ResourceModel\Setup;

class LogHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Kaufland\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installListingLogTable($setup);
        $this->installSynchronizationLogTable($setup);
        $this->installSystemLogTable($setup);
        $this->installOrderLogTableTable($setup);
    }

    private function installListingLogTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_LOG);

        $listingLogTable = $setup->getConnection()->newTable($tableName);

        $listingLogTable
            ->addColumn(
                ListingLogResource::COLUMN_ID,
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
                ListingLogResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingLogResource::COLUMN_STOREFRONT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingLogResource::COLUMN_LISTING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingLogResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_LISTING_TITLE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_PRODUCT_TITLE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_ACTION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingLogResource::COLUMN_ACTION,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                ListingLogResource::COLUMN_INITIATOR,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingLogResource::COLUMN_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                ListingLogResource::COLUMN_DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('action', ListingLogResource::COLUMN_ACTION)
            ->addIndex('action_id', ListingLogResource::COLUMN_ACTION_ID)
            ->addIndex('initiator', ListingLogResource::COLUMN_INITIATOR)
            ->addIndex('listing_id', ListingLogResource::COLUMN_LISTING_ID)
            ->addIndex('listing_product_id', ListingLogResource::COLUMN_LISTING_PRODUCT_ID)
            ->addIndex('listing_title', ListingLogResource::COLUMN_LISTING_TITLE)
            ->addIndex('product_id', ListingLogResource::COLUMN_PRODUCT_ID)
            ->addIndex('product_title', ListingLogResource::COLUMN_PRODUCT_TITLE)
            ->addIndex('type', ListingLogResource::COLUMN_TYPE)
            ->addIndex('account_id', ListingLogResource::COLUMN_ACCOUNT_ID)
            ->addIndex('storefront_id', ListingLogResource::COLUMN_STOREFRONT_ID)
            ->addIndex('create_date', ListingLogResource::COLUMN_CREATE_DATE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($listingLogTable);
    }

    private function installSynchronizationLogTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_SYNCHRONIZATION_LOG);

        $synchronizationLogTable = $setup->getConnection()->newTable($tableName);

        $synchronizationLogTable
            ->addColumn(
                SyncLogResource::COLUMN_ID,
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
                SyncLogResource::COLUMN_OPERATION_HISTORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                SyncLogResource::COLUMN_TASK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SyncLogResource::COLUMN_INITIATOR,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SyncLogResource::COLUMN_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                SyncLogResource::COLUMN_DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                SyncLogResource::COLUMN_DETAILED_DESCRIPTION,
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                'additional_data',
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                SyncLogResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('initiator', SyncLogResource::COLUMN_INITIATOR)
            ->addIndex('task', SyncLogResource::COLUMN_TASK)
            ->addIndex('operation_history_id', SyncLogResource::COLUMN_OPERATION_HISTORY_ID)
            ->addIndex('type', SyncLogResource::COLUMN_TYPE)
            ->addIndex('create_date', SyncLogResource::COLUMN_CREATE_DATE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($synchronizationLogTable);
    }

    private function installSystemLogTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_SYSTEM_LOG);

        $systemLogTable = $setup->getConnection()->newTable($tableName);

        $systemLogTable
            ->addColumn(
                LogSystemResource::COLUMN_ID,
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
                LogSystemResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_CLASS,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_DETAILED_DESCRIPTION,
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('type', LogSystemResource::COLUMN_TYPE)
            ->addIndex('class', LogSystemResource::COLUMN_CLASS)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($systemLogTable);
    }

    private function installOrderLogTableTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_LOG);

        $orderLogTable = $setup->getConnection()->newTable($tableName);

        $orderLogTable
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
                'account_id',
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
                'order_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                'type',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 2,
                ]
            )
            ->addColumn(
                'initiator',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 2,
                ]
            )
            ->addColumn(
                'description',
                Table::TYPE_TEXT
            )
            ->addColumn(
                'additional_data',
                Table::TYPE_VARBINARY
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME
            )
            ->addIndex('account_id', 'account_id')
            ->addIndex('storefront_id', 'storefront_id')
            ->addIndex('order_id', 'order_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($orderLogTable);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }
}
