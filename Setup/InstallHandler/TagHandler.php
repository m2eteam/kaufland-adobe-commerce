<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\InstallHandler;

use M2E\Kaufland\Helper\Module\Database\Tables as TablesHelper;
use M2E\Kaufland\Model\ResourceModel\Tag as TagResource;
use M2E\Kaufland\Model\ResourceModel\Tag\ListingProduct\Relation as TagProductRelationResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class TagHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Kaufland\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installTagTable($setup);
        $this->installTagRelationTable($setup);
    }

    private function installTagTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_TAG);

        $tagTable = $setup->getConnection()->newTable($tableName);
        $tagTable
            ->addColumn(
                TagResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
        ->addColumn(
            TagResource::COLUMN_ERROR_CODE,
            Table::TYPE_TEXT,
            100,
            ['nullable' => false]
        )
        ->addColumn(
            TagResource::COLUMN_TEXT,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        )
        ->addColumn(
            TagResource::COLUMN_CREATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false]
        )
        ->addIndex(
            'error_code',
            TagResource::COLUMN_ERROR_CODE,
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE],
        );
        $tagTable->setOption('type', 'INNODB');
        $tagTable->setOption('charset', 'utf8');
        $tagTable->setOption('collate', 'utf8_general_ci');
        $tagTable->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($tagTable);
    }

    private function installTagRelationTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_TAG_RELATION);

        $tagRelationTable = $setup->getConnection()->newTable($tableName);

        $tagRelationTable
            ->addColumn(
                TagProductRelationResource::COLUMN_ID,
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
                TagProductRelationResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                TagProductRelationResource::COLUMN_TAG_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                TagProductRelationResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            );
        $tagRelationTable->addIndex('listing_product_id', TagProductRelationResource::COLUMN_LISTING_PRODUCT_ID);
        $tagRelationTable->addIndex('tag_id', TagProductRelationResource::COLUMN_TAG_ID);
        $tagRelationTable->setOption('type', 'INNODB');
        $tagRelationTable->setOption('charset', 'utf8');
        $tagRelationTable->setOption('collate', 'utf8_general_ci');
        $tagRelationTable->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($tagRelationTable);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tagCreateDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $tagCreateDate = $tagCreateDate->format('Y-m-d H:i:s');

        $setup->getConnection()->insertMultiple(
            $this->getFullTableName(TablesHelper::TABLE_NAME_TAG),
            [
                [
                    'error_code' => 'has_error',
                    'text' => 'Has error',
                    'create_date' => $tagCreateDate,
                ],
            ]
        );
    }
}
