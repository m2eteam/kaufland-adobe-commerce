<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m05;

use M2E\Kaufland\Model\ResourceModel\Product\Lock as ProductLockResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class AddProductLockTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $productLockTable = $this->getConnection()->newTable(
            $this->getFullTableName(\M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT_LOCK)
        );
        $productLockTable
            ->addColumn(
                ProductLockResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                ProductLockResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                ProductLockResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ProductLockResource::COLUMN_INITIATOR,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                ProductLockResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('id', ProductLockResource::COLUMN_ID)
            ->addIndex('product_id', ProductLockResource::COLUMN_PRODUCT_ID)
            ->addIndex(
                'product_id__type',
                [ProductLockResource::COLUMN_PRODUCT_ID, ProductLockResource::COLUMN_TYPE],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($productLockTable);
    }
}
