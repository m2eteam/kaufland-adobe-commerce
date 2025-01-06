<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m03;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Warehouse as WarehouseResourceModel;
use Magento\Framework\DB\Ddl\Table;

class AddWarehouseTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $warehouseTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_WAREHOUSE));

        $warehouseTable
            ->addColumn(
                WarehouseResourceModel::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ],
            )
            ->addColumn(
                WarehouseResourceModel::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false,]
            )
            ->addColumn(
                WarehouseResourceModel::COLUMN_WAREHOUSE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false,]
            )
            ->addColumn(
                WarehouseResourceModel::COLUMN_NAME,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false,]
            )
            ->addColumn(
                WarehouseResourceModel::COLUMN_IS_DEFAULT,
                Table::TYPE_SMALLINT,
                2,
                ['nullable' => false,]
            )
            ->addColumn(
                WarehouseResourceModel::COLUMN_TYPE,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false,]
            )
            ->addColumn(
                WarehouseResourceModel::COLUMN_ADDRESS,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false,]
            )
            ->addColumn(
                WarehouseResourceModel::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                WarehouseResourceModel::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('account_id', 'account_id')
            ->addIndex('warehouse_id', 'warehouse_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($warehouseTable);
    }

}
