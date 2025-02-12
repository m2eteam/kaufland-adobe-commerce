<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\InstallHandler;

use M2E\Core\Model\ResourceModel\Setup;
use M2E\Kaufland\Helper\Module\Database\Tables as TablesHelper;
use M2E\Kaufland\Model\ResourceModel\AttributeMapping\Pair as PairResource;
use M2E\Kaufland\Model\ResourceModel\Category\Attribute as CategoryAttributeResource;
use M2E\Kaufland\Model\ResourceModel\Category\Dictionary as CategoryDictionaryResource;
use M2E\Kaufland\Model\ResourceModel\Category\Tree as CategoryTreeResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class CategoryHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Kaufland\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installCategoryTreeTable($setup);
        $this->installCategoryDictionaryTable($setup);
        $this->installCategoryAttributesTable($setup);
        $this->installCategoryAttributesMappingTable($setup);
    }

    private function installCategoryTreeTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_CATEGORY_TREE);

        $categoryTreeTable = $setup->getConnection()->newTable($tableName);
        $categoryTreeTable->addColumn(
            CategoryTreeResource::ID_FIELD,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $categoryTreeTable->addColumn(
            CategoryTreeResource::COLUMN_STOREFRONT_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $categoryTreeTable->addColumn(
            CategoryTreeResource::COLUMN_CATEGORY_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $categoryTreeTable->addColumn(
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $categoryTreeTable->addColumn(
            CategoryTreeResource::COLUMN_TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $categoryTreeTable->addIndex('parent_category_id', 'parent_category_id');
        $categoryTreeTable->addIndex('storefront_id', 'storefront_id');

        $setup->getConnection()->createTable($categoryTreeTable);
    }

    private function installCategoryDictionaryTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_CATEGORY_DICTIONARY);

        $categoryDictionaryTable = $setup->getConnection()->newTable($tableName);

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
            Setup::LONG_COLUMN_SIZE
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

        $setup->getConnection()->createTable($categoryDictionaryTable);
    }

    private function installCategoryAttributesTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_CATEGORY_ATTRIBUTES);

        $categoryAttributesTable = $setup->getConnection()->newTable($tableName);
        $categoryAttributesTable->addColumn(
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
        $categoryAttributesTable->addColumn(
            CategoryAttributeResource::COLUMN_CATEGORY_DICTIONARY_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $categoryAttributesTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_TYPE,
            Table::TYPE_TEXT,
            30
        );
        $categoryAttributesTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_ID,
            Table::TYPE_TEXT,
            30,
        );
        $categoryAttributesTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_NICK,
            Table::TYPE_TEXT,
            50
        );
        $categoryAttributesTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_TITLE,
            Table::TYPE_TEXT,
            50
        );
        $categoryAttributesTable->addColumn(
            CategoryAttributeResource::COLUMN_ATTRIBUTE_DESCRIPTION,
            Table::TYPE_TEXT,
            50
        );
        $categoryAttributesTable->addColumn(
            CategoryAttributeResource::COLUMN_VALUE_MODE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $categoryAttributesTable->addColumn(
            CategoryAttributeResource::COLUMN_VALUE_RECOMMENDED,
            Table::TYPE_TEXT,
            Setup::LONG_COLUMN_SIZE
        );
        $categoryAttributesTable->addColumn(
            CategoryAttributeResource::COLUMN_VALUE_CUSTOM_VALUE,
            Table::TYPE_TEXT,
            255,
        );
        $categoryAttributesTable->addColumn(
            CategoryAttributeResource::COLUMN_VALUE_CUSTOM_ATTRIBUTE,
            Table::TYPE_TEXT,
            255,
        );
        $categoryAttributesTable->addIndex(
            'category_dictionary_id',
            CategoryAttributeResource::COLUMN_CATEGORY_DICTIONARY_ID,
        );

        $setup->getConnection()->createTable($categoryAttributesTable);
    }

    private function installCategoryAttributesMappingTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_ATTRIBUTE_MAPPING);

        $attributeMappingTable = $setup->getConnection()->newTable($tableName);
        $attributeMappingTable
            ->addColumn(
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
            $attributeMappingTable->addColumn(
                PairResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            );
            $attributeMappingTable->addColumn(
                PairResource::COLUMN_CHANNEL_ATTRIBUTE_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            );
            $attributeMappingTable->addColumn(
                PairResource::COLUMN_CHANNEL_ATTRIBUTE_CODE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            );
            $attributeMappingTable->addColumn(
                PairResource::COLUMN_MAGENTO_ATTRIBUTE_CODE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            );
            $attributeMappingTable->addColumn(
                PairResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            );
            $attributeMappingTable->addColumn(
                PairResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            );
            $attributeMappingTable
                ->addIndex('type', PairResource::COLUMN_TYPE)
                ->addIndex('create_date', PairResource::COLUMN_CREATE_DATE)
                ->setOption('type', 'INNODB')
                ->setOption('charset', 'utf8')
                ->setOption('collate', 'utf8_general_ci')
                ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($attributeMappingTable);;
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }
}
