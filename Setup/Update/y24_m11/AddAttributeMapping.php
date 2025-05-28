<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m11;

use Magento\Framework\DB\Ddl\Table;

class AddAttributeMapping extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $newTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName('m2e_kaufland_attribute_mapping'));

        $newTable->addColumn(
                'id',
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
            'type',
            Table::TYPE_TEXT,
            100,
            ['nullable' => false]
        );
        $newTable->addColumn(
            'channel_attribute_title',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $newTable->addColumn(
            'channel_attribute_code',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $newTable->addColumn(
            'magento_attribute_code',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $newTable->addColumn(
            'update_date',
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $newTable->addColumn(
            'create_date',
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $newTable->addIndex('type', 'type');
        $newTable->addIndex('create_date', 'create_date');

        $newTable
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($newTable);
    }
}
