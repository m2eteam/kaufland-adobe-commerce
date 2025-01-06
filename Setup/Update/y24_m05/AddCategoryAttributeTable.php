<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m05;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Category\Attribute as CategoryAttributeResource;
use Magento\Framework\DB\Ddl\Table;

class AddCategoryAttributeTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public const LONG_COLUMN_SIZE = 16777217;

    public function execute(): void
    {
        $categoryAttributeTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_CATEGORY_ATTRIBUTES));

        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_CATEGORY_DICTIONARY_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_TYPE,
            Table::TYPE_TEXT,
            30
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_ID,
            Table::TYPE_TEXT,
            30,
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_NICK,
            Table::TYPE_TEXT,
            50
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_NICK,
            Table::TYPE_TEXT,
            50
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_TITLE,
            Table::TYPE_TEXT,
            50
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_DESCRIPTION,
            Table::TYPE_TEXT,
            50
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_VALUE_MODE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_VALUE_RECOMMENDED,
            Table::TYPE_TEXT,
            self::LONG_COLUMN_SIZE
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_VALUE_CUSTOM_VALUE,
            Table::TYPE_TEXT,
            255,
        );
        $categoryAttributeTable->addColumn(
            CategoryAttributeResource::COLUMN_VALUE_CUSTOM_ATTRIBUTE,
            Table::TYPE_TEXT,
            255,
        );
        $categoryAttributeTable->addIndex(
            'category_dictionary_id',
            CategoryAttributeResource::COLUMN_CATEGORY_DICTIONARY_ID,
        );
        $categoryAttributeTable
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($categoryAttributeTable);
    }
}
