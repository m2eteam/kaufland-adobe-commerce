<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\InstallHandler;

use M2E\Kaufland\Helper\Module\Database\Tables as TablesHelper;
use Magento\Framework\DB\Ddl\Table;
use M2E\Core\Model\ResourceModel\Setup;

class ProcessingHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Kaufland\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installProcessingTableTable($setup);
        $this->installProcessingPartialDataTable($setup);
        $this->installProcessingLockTable($setup);
    }

    private function installProcessingTableTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PROCESSING);

        $processingTable = $setup->getConnection()->newTable($tableName);

        $processingTable
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
                'type',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                'server_hash',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'stage',
                Table::TYPE_TEXT,
                20,
                ['nullable' => false]
            )
            ->addColumn(
                'handler_nick',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'params',
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                'result_data',
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                'result_messages',
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                'data_next_part',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true]
            )
            ->addColumn(
                'is_completed',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                'expiration_date',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
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
            ->addIndex('type', 'type')
            ->addIndex('stage', 'stage')
            ->addIndex('is_completed', 'is_completed')
            ->addIndex('expiration_date', 'expiration_date')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($processingTable);
    }

    private function installProcessingPartialDataTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PROCESSING_PARTIAL_DATA);

        $processingPartialDataTable = $setup->getConnection()->newTable($tableName);
        $processingPartialDataTable
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
                'processing_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'part_number',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'data',
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addIndex('part_number', 'part_number')
            ->addIndex('processing_id', 'processing_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $setup->getConnection()->createTable($processingPartialDataTable);
    }

    private function installProcessingLockTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PROCESSING_LOCK);

        $processingLockTable = $setup->getConnection()->newTable($tableName);
        $processingLockTable
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
                'processing_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'object_nick',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'object_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'tag',
                Table::TYPE_TEXT,
                255,
                ['default' => null]
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
            ->addIndex('processing_id', 'processing_id')
            ->addIndex('object_nick', 'object_nick')
            ->addIndex('object_id', 'object_id')
            ->addIndex('tag', 'tag')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($processingLockTable);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }
}
