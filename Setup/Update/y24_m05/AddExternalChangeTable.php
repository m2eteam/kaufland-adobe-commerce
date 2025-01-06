<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m05;

use M2E\Kaufland\Helper\Module\Database\Tables as TablesHelper;
use M2E\Kaufland\Model\ResourceModel\ExternalChange as ExternalChangeResource;
use Magento\Framework\DB\Ddl\Table;

class AddExternalChangeTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createExternalChangeTable();
    }

    private function createExternalChangeTable(): void
    {
        $externalChangeTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_EXTERNAL_CHANGE));

        $externalChangeTable
            ->addColumn(
                ExternalChangeResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ],
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_STOREFRONT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_UNIT_ID,
                Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_OFFER_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addIndex('account_id', ExternalChangeResource::COLUMN_ACCOUNT_ID)
            ->addIndex('storefront_id', ExternalChangeResource::COLUMN_STOREFRONT_ID)
            ->addIndex('unit_id', ExternalChangeResource::COLUMN_UNIT_ID)
            ->addIndex('offer_id', ExternalChangeResource::COLUMN_OFFER_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($externalChangeTable);
    }

}
