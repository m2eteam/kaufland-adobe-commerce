<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\InstallHandler;

use M2E\Kaufland\Helper\Module\Database\Tables as TablesHelper;
use M2E\Kaufland\Model\ResourceModel\Template\Description as DescriptionResource;
use M2E\Kaufland\Model\ResourceModel\Template\Shipping as ShippingResource;
use M2E\Kaufland\Model\ResourceModel\Template\Synchronization as SynchronizationResource;
use Magento\Framework\DB\Ddl\Table;
use M2E\Core\Model\ResourceModel\Setup;

class PolicyHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Kaufland\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installSellingFormatTable($setup);
        $this->installSynchronizationTable($setup);
        $this->installDescriptionTable($setup);
        $this->installShippingTable($setup);
    }

    private function installSellingFormatTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_TEMPLATE_SELLING_FORMAT);

        $sellingFormatTable = $setup->getConnection()->newTable($tableName);

        $sellingFormatTable
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_ID,
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
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_IS_CUSTOM_TEMPLATE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_CUSTOM_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_CUSTOM_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_PERCENTAGE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 100]
            )
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_MODIFICATION_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_MIN_POSTED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_MAX_POSTED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_FIXED_PRICE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_FIXED_PRICE_MODIFIER,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_FIXED_PRICE_CUSTOM_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'is_custom_template',
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_IS_CUSTOM_TEMPLATE
            )
            ->addIndex(
                'title',
                \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_TITLE
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($sellingFormatTable);
    }

    private function installSynchronizationTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_TEMPLATE_SYNCHRONIZATION);

        $synchronizationTable = $setup->getConnection()->newTable($tableName);
        $synchronizationTable
            ->addColumn(
                SynchronizationResource::COLUMN_ID,
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
                SynchronizationResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_IS_CUSTOM_TEMPLATE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_STATUS_ENABLED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_IS_IN_STOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_QTY_CALCULATED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_QTY_CALCULATED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_ADVANCED_RULES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_ADVANCED_RULES_FILTERS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_QTY,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_QTY_MAX_APPLIED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_PRICE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_TITLE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_CATEGORIES,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_IMAGES,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_DESCRIPTION,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_OTHER,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_FILTER_USER_LOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_STATUS_ENABLED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_IS_IN_STOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_QTY_CALCULATED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_QTY_CALCULATED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_ADVANCED_RULES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_ADVANCED_RULES_FILTERS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_STATUS_DISABLED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_OUT_OFF_STOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_QTY_CALCULATED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_QTY_CALCULATED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_ADVANCED_RULES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_ADVANCED_RULES_FILTERS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'title',
                SynchronizationResource::COLUMN_TITLE
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($synchronizationTable);
    }

    private function installDescriptionTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_TEMPLATE_DESCRIPTION);

        $descriptionTable = $setup->getConnection()->newTable($tableName);
        $descriptionTable
            ->addColumn(
                DescriptionResource::COLUMN_ID,
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
                DescriptionResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_IS_CUSTOM_TEMPLATE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_TITLE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_TITLE_TEMPLATE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_DESCRIPTION_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_DESCRIPTION_TEMPLATE,
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_IMAGE_MAIN_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_IMAGE_MAIN_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 4]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_IMAGES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_IMAGES_LIMIT,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_IMAGES_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                DescriptionResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'is_custom_template',
                DescriptionResource::COLUMN_IS_CUSTOM_TEMPLATE
            )
            ->addIndex(
                'title',
                DescriptionResource::COLUMN_TITLE
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($descriptionTable);
    }

    private function installShippingTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_TEMPLATE_SHIPPING);

        $shippingTable = $setup->getConnection()->newTable($tableName);
        $shippingTable
            ->addColumn(
                ShippingResource::COLUMN_ID,
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
                ShippingResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ShippingResource::COLUMN_IS_CUSTOM_TEMPLATE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ShippingResource::COLUMN_HANDLING_TIME,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ShippingResource::COLUMN_HANDLING_TIME_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                ShippingResource::COLUMN_HANDLING_TIME_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ShippingResource::COLUMN_WAREHOUSE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ShippingResource::COLUMN_SHIPPING_GROUP_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ShippingResource::COLUMN_STOREFRONT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ShippingResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                ShippingResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            );
        $shippingTable->addIndex('title', ShippingResource::COLUMN_TITLE)
                      ->setOption('type', 'INNODB')
                      ->setOption('charset', 'utf8')
                      ->setOption('collate', 'utf8_general_ci')
                      ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($shippingTable);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }
}
