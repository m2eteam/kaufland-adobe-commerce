<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m11;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\AttributeMapping\Pair as PairResource;
use Magento\Framework\DB\Ddl\Table;

class AddAttributeMapping extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $newTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_ATTRIBUTE_MAPPING));

        $newTable->addColumn(
                PairResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
        );
        $newTable->addColumn(
            PairResource::COLUMN_TYPE,
            Table::TYPE_TEXT,
            100,
            ['nullable' => false]
        );
        $newTable->addColumn(
            PairResource::COLUMN_CHANNEL_ATTRIBUTE_TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $newTable->addColumn(
            PairResource::COLUMN_CHANNEL_ATTRIBUTE_CODE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $newTable->addColumn(
            PairResource::COLUMN_MAGENTO_ATTRIBUTE_CODE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $newTable->addColumn(
            PairResource::COLUMN_UPDATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $newTable->addColumn(
            PairResource::COLUMN_CREATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $newTable->addIndex('type', PairResource::COLUMN_TYPE);
        $newTable->addIndex('create_date', PairResource::COLUMN_CREATE_DATE);

        $newTable
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($newTable);
    }
}
