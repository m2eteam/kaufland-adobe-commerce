<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\InstallHandler;

use M2E\Kaufland\Helper\Module\Database\Tables as TablesHelper;
use M2E\Kaufland\Model\ResourceModel\ExternalChange as ExternalChangeResource;
use M2E\Kaufland\Model\ResourceModel\Instruction as ProductInstructionResource;
use M2E\Kaufland\Model\ResourceModel\Listing\Other as OtherListingResource;
use M2E\Kaufland\Model\ResourceModel\Product as ProductResource;
use M2E\Kaufland\Model\ResourceModel\Product\Lock as ProductLockResource;
use M2E\Kaufland\Model\ResourceModel\ScheduledAction as ScheduledActionResource;
use M2E\Kaufland\Model\ResourceModel\StopQueue as StopQueueResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use M2E\Core\Model\ResourceModel\Setup;

class ProductHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Kaufland\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installProductTable($setup);
        $this->installProductInstructionTable($setup);
        $this->installProductScheduledActionTable($setup);
        $this->installProductLockTableTable($setup);
        $this->installStopQueueTable($setup);
        $this->installListingOtherTable($setup);
        $this->installExternalChangesTable($setup);
    }

    private function installProductTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT);

        $productTable = $setup->getConnection()->newTable($tableName);

        $productTable
            ->addColumn(
                ProductResource::COLUMN_ID,
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
                ProductResource::COLUMN_LISTING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductResource::COLUMN_KAUFLAND_PRODUCT_ID,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                ProductResource::COLUMN_IS_KAUFLAND_PRODUCT_CREATOR,
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0]
            )
            ->addColumn(
                ProductResource::COLUMN_UNIT_ID,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                ProductResource::COLUMN_STOREFRONT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductResource::COLUMN_OFFER_ID,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                ProductResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ProductResource::COLUMN_IS_INCOMPLETE,
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0]
            )
            ->addColumn(
                ProductResource::COLUMN_STATUS_CHANGE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_STATUS_CHANGER,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_HANDLING_TIME,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_WAREHOUSE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_SHIPPING_GROUP_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_CONDITION,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_CATEGORY_ID,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_CATEGORIES_DATA,
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_CATEGORIES_ATTRIBUTES_DATA,
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_TITLE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_DESCRIPTION,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_IMAGE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_IS_VALID_CATEGORY_ATTRIBUTES,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => null],
            )
            ->addColumn(
                ProductResource::COLUMN_CATEGORY_ATTRIBUTES_ERRORS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_CHANNEL_PRODUCT_EMPTY_ATTRIBUTES,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('listing_id', ProductResource::COLUMN_LISTING_ID)
            ->addIndex('product_id', ProductResource::COLUMN_KAUFLAND_PRODUCT_ID)
            ->addIndex('status', ProductResource::COLUMN_STATUS)
            ->addIndex('status_changer', ProductResource::COLUMN_STATUS_CHANGER)
            ->addIndex('online_category_id', ProductResource::COLUMN_ONLINE_CATEGORY_ID)
            ->addIndex('online_qty', ProductResource::COLUMN_ONLINE_QTY)
            ->addIndex('online_price', ProductResource::COLUMN_ONLINE_PRICE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($productTable);
    }

    private function installProductInstructionTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_INSTRUCTION);

        $productInstruction = $setup->getConnection()->newTable($tableName);
        $productInstruction
            ->addColumn(
                ProductInstructionResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_INITIATOR,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_PRIORITY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_SKIP_UNTIL,
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
            ->addIndex('listing_product_id', ProductInstructionResource::COLUMN_LISTING_PRODUCT_ID)
            ->addIndex('type', ProductInstructionResource::COLUMN_TYPE)
            ->addIndex('priority', ProductInstructionResource::COLUMN_PRIORITY)
            ->addIndex('skip_until', ProductInstructionResource::COLUMN_SKIP_UNTIL)
            ->addIndex('create_date', ProductInstructionResource::COLUMN_CREATE_DATE)
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($productInstruction);
    }

    private function installProductScheduledActionTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_SCHEDULED_ACTION);

        $productScheduledAction = $setup->getConnection()->newTable($tableName);
        $productScheduledAction
            ->addColumn(
                ScheduledActionResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_ACTION_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_STATUS_CHANGER,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0, 'unsigned' => true]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_IS_FORCE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_TAG,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'listing_product_id__action_type',
                [ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID, ScheduledActionResource::COLUMN_ACTION_TYPE],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex('action_type', ScheduledActionResource::COLUMN_ACTION_TYPE)
            ->addIndex('tag', ScheduledActionResource::COLUMN_TAG)
            ->addIndex('create_date', ScheduledActionResource::COLUMN_CREATE_DATE)
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($productScheduledAction);
    }

    private function installProductLockTableTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_LOCK);

        $productLockTable = $setup->getConnection()->newTable($tableName);
        $productLockTable
            ->addColumn(
                ProductLockResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                ProductLockResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                ProductLockResource::COLUMN_INITIATOR,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                ProductLockResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ProductLockResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('id', ProductLockResource::COLUMN_ID)
            ->addIndex('product_id', ProductLockResource::COLUMN_PRODUCT_ID)
            ->addIndex(
                'product_id__type',
                [ProductLockResource::COLUMN_PRODUCT_ID, ProductLockResource::COLUMN_TYPE],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($productLockTable);
    }

    private function installStopQueueTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_STOP_QUEUE);

        $stopQueueTable = $setup->getConnection()->newTable($tableName);
        $stopQueueTable
            ->addColumn(
                StopQueueResource::COLUMN_ID,
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
                StopQueueResource::COLUMN_IS_PROCESSED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                StopQueueResource::COLUMN_REQUEST_DATA,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                StopQueueResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                StopQueueResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('is_processed', 'is_processed')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($stopQueueTable);
    }

    private function installListingOtherTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_OTHER);

        $listingOtherTable = $setup->getConnection()->newTable($tableName);
        $listingOtherTable
            ->addColumn(
                OtherListingResource::COLUMN_ID,
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
                OtherListingResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OtherListingResource::COLUMN_STOREFRONT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OtherListingResource::COLUMN_UNIT_ID,
                Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OtherListingResource::COLUMN_OFFER_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )
            ->addColumn(
                OtherListingResource::COLUMN_KAUFLAND_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                OtherListingResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                OtherListingResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                OtherListingResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OtherListingResource::COLUMN_HANDLING_TIME,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OtherListingResource::COLUMN_WAREHOUSE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                OtherListingResource::COLUMN_SHIPPING_GROUP_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                OtherListingResource::COLUMN_CONDITION,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OtherListingResource::COLUMN_EANS,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OtherListingResource::COLUMN_CURRENCY_CODE,
                Table::TYPE_TEXT,
                10,
                ['default' => null]
            )
            ->addColumn(
                OtherListingResource::COLUMN_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                OtherListingResource::COLUMN_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                OtherListingResource::COLUMN_MAIN_PICTURE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                OtherListingResource::COLUMN_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['default' => null]
            )
            ->addColumn(
                OtherListingResource::COLUMN_CATEGORY_TITLE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                OtherListingResource::COLUMN_FULFILLED_BY_MERCHANT,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                OtherListingResource::COLUMN_MOVED_TO_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                OtherListingResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                OtherListingResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('account_id', 'account_id')
            ->addIndex('storefront_id', 'storefront_id')
            ->addIndex('kaufland_product_id', 'kaufland_product_id')
            ->addIndex('unit_id', 'unit_id')
            ->addIndex('magento_product_id', 'magento_product_id')
            ->addIndex('status', 'status')
            ->addIndex('title', 'title')
            ->addIndex('eans', 'eans')
            ->addIndex('currency_code', 'currency_code')
            ->addIndex('price', 'price')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($listingOtherTable);
    }

    private function installExternalChangesTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_EXTERNAL_CHANGE);

        $externalChangeTable = $setup->getConnection()->newTable($tableName);
        $externalChangeTable
            ->addColumn(
                ExternalChangeResource::COLUMN_ID,
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
                ExternalChangeResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_STOREFRONT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_UNIT_ID,
                Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_OFFER_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('account_id', ExternalChangeResource::COLUMN_ACCOUNT_ID)
            ->addIndex('storefront_id', ExternalChangeResource::COLUMN_STOREFRONT_ID)
            ->addIndex('unit_id', ExternalChangeResource::COLUMN_UNIT_ID)
            ->addIndex('offer_id', ExternalChangeResource::COLUMN_OFFER_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($externalChangeTable);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }
}
