<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\InstallHandler;

use M2E\Kaufland\Helper\Module\Database\Tables as TablesHelper;
use M2E\Kaufland\Model\ResourceModel\Account as AccountResource;
use M2E\Kaufland\Model\ResourceModel\ShippingGroup as ShippingGroupResource;
use M2E\Kaufland\Model\ResourceModel\Storefront as StorefrontResource;
use M2E\Kaufland\Model\ResourceModel\Warehouse as WarehouseResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class AccountHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Kaufland\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installAccountTable($setup);
        $this->installStorefrontTable($setup);
        $this->installWarehouseTable($setup);
        $this->installShippingGroupTable($setup);
    }

    private function installAccountTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_ACCOUNT);

        $accountTable = $setup->getConnection()->newTable($tableName);

        $accountTable
            ->addColumn(
                AccountResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                AccountResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                AccountResource::COLUMN_SERVER_HASH,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            )
            ->addColumn(
                AccountResource::COLUMN_IDENTIFIER,
                Table::TYPE_TEXT,
                100,
                ['default' => null]
            )
            ->addColumn(
                AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                AccountResource::COLUMN_CREATE_MAGENTO_INVOICE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                AccountResource::COLUMN_UPLOAD_MAGENTO_INVOICE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                AccountResource::COLUMN_CREATE_MAGENTO_SHIPMENT,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                AccountResource::COLUMN_OTHER_LISTINGS_SYNCHRONIZATION,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_SETTINGS,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => '[]']
            )
            ->addColumn(
                AccountResource::COLUMN_OTHER_LISTINGS_RELATED_STORE_ID,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                AccountResource::COLUMN_ORDER_LAST_SYNC,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                AccountResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                AccountResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('title', AccountResource::COLUMN_TITLE,)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($accountTable);
    }

    private function installStorefrontTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_STOREFRONT);

        $storefrontTable = $setup->getConnection()->newTable($tableName);

        $storefrontTable->addColumn(
            StorefrontResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $storefrontTable->addColumn(
            StorefrontResource::COLUMN_ACCOUNT_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $storefrontTable->addColumn(
            StorefrontResource::COLUMN_STOREFRONT_CODE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $storefrontTable->addColumn(
            StorefrontResource::COLUMN_INVENTORY_LAST_SYNC,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $storefrontTable->addColumn(
            StorefrontResource::COLUMN_UPDATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $storefrontTable->addColumn(
            StorefrontResource::COLUMN_CREATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $storefrontTable->addIndex(
            'storefront_code__account_id',
            ['storefront_code', 'account_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );

        $setup->getConnection()->createTable($storefrontTable);
    }

    private function installWarehouseTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_WAREHOUSE);

        $warehouseTable = $setup->getConnection()->newTable($tableName);

        $warehouseTable->addColumn(
            WarehouseResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $warehouseTable->addColumn(
            WarehouseResource::COLUMN_ACCOUNT_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $warehouseTable->addColumn(
            WarehouseResource::COLUMN_WAREHOUSE_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $warehouseTable->addColumn(
            WarehouseResource::COLUMN_NAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $warehouseTable->addColumn(
            WarehouseResource::COLUMN_IS_DEFAULT,
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false,]
        );
        $warehouseTable->addColumn(
            WarehouseResource::COLUMN_TYPE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $warehouseTable->addColumn(
            WarehouseResource::COLUMN_ADDRESS,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $warehouseTable->addColumn(
            WarehouseResource::COLUMN_UPDATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $warehouseTable ->addColumn(
            WarehouseResource::COLUMN_CREATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $warehouseTable->addIndex('account_id', 'account_id')
                       ->addIndex('warehouse_id', 'warehouse_id')
                       ->setOption('type', 'INNODB')
                       ->setOption('charset', 'utf8')
                       ->setOption('collate', 'utf8_general_ci')
                       ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($warehouseTable);
    }

    private function installShippingGroupTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_SHIPPING_GROUP);

        $shippingGroupTable = $setup->getConnection()->newTable($tableName);

        $shippingGroupTable->addColumn(
            ShippingGroupResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $shippingGroupTable->addColumn(
            ShippingGroupResource::COLUMN_SHIPPING_GROUP_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $shippingGroupTable->addColumn(
            ShippingGroupResource::COLUMN_ACCOUNT_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $shippingGroupTable->addColumn(
            ShippingGroupResource::COLUMN_STOREFRONT_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $shippingGroupTable->addColumn(
            ShippingGroupResource::COLUMN_CURRENCY,
            Table::TYPE_TEXT,
            50,
            ['nullable' => false,]
        );
        $shippingGroupTable->addColumn(
            ShippingGroupResource::COLUMN_NAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $shippingGroupTable->addColumn(
            ShippingGroupResource::COLUMN_TYPE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $shippingGroupTable->addColumn(
            ShippingGroupResource::COLUMN_IS_DEFAULT,
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false,]
        );
        $shippingGroupTable->addColumn(
            ShippingGroupResource::COLUMN_REGIONS,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $shippingGroupTable->addColumn(
            ShippingGroupResource::COLUMN_UPDATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $shippingGroupTable->addColumn(
            ShippingGroupResource::COLUMN_CREATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );

        $shippingGroupTable ->addIndex('account_id', 'account_id')
                            ->addIndex('storefront_id', 'storefront_id')
                            ->setOption('type', 'INNODB')
                            ->setOption('charset', 'utf8')
                            ->setOption('collate', 'utf8_general_ci')
                            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($shippingGroupTable);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }
}
