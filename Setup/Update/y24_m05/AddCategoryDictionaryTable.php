<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m05;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Category\Dictionary as CategoryDictionaryResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class AddCategoryDictionaryTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public const LONG_COLUMN_SIZE = 16777217;

    public function execute(): void
    {
        $categoryDictionaryTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_CATEGORY_DICTIONARY));

        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_STOREFRONT_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_CATEGORY_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_STATE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_PATH,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_PRODUCT_ATTRIBUTES,
            Table::TYPE_TEXT,
            self::LONG_COLUMN_SIZE
        );
        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_TOTAL_PRODUCT_ATTRIBUTES,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_USED_PRODUCT_ATTRIBUTES,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_HAS_REQUIRED_PRODUCT_ATTRIBUTES,
            Table::TYPE_BOOLEAN,
            null,
            ['default' => 0]
        );
        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_UPDATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $categoryDictionaryTable->addColumn(
            CategoryDictionaryResource::COLUMN_CREATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $categoryDictionaryTable->addIndex(
            'storefront_id__category_id',
            ['storefront_id', 'category_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );
        $categoryDictionaryTable
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($categoryDictionaryTable);
    }
}
