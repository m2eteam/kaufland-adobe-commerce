<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m05;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Category\Tree as CategoryTreeResource;
use Magento\Framework\DB\Ddl\Table;

class AddCategoryTreeTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $categoryTreeTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_CATEGORY_TREE));

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
        $categoryTreeTable
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($categoryTreeTable);
    }
}
