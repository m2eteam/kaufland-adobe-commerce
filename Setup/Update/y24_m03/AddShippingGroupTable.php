<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m03;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\ShippingGroup as ShippingGroupResource;
use Magento\Framework\DB\Ddl\Table;

class AddShippingGroupTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $shippingGroupTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_SHIPPING_GROUP));

        $shippingGroupTable
            ->addColumn(
                ShippingGroupResource::COLUMN_ID,
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
                ShippingGroupResource::COLUMN_SHIPPING_GROUP_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                ]
            )
            ->addColumn(
                ShippingGroupResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                ShippingGroupResource::COLUMN_STOREFRONT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                ShippingGroupResource::COLUMN_CURRENCY,
                Table::TYPE_TEXT,
                50,
                [
                    'nullable' => false,
                ]
            )
            ->addColumn(
                ShippingGroupResource::COLUMN_NAME,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false,
                ]
            )
            ->addColumn(
                ShippingGroupResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                100,
                [
                    'nullable' => false,
                ]
            )
            ->addColumn(
                ShippingGroupResource::COLUMN_IS_DEFAULT,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                ]
            )
            ->addColumn(
                ShippingGroupResource::COLUMN_REGIONS,
                Table::TYPE_TEXT,
                100,
                [
                    'nullable' => false,
                ]
            )
            ->addColumn(
                ShippingGroupResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ShippingGroupResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('account_id', 'account_id')
            ->addIndex('storefront_id', 'storefront_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($shippingGroupTable);
    }
}
