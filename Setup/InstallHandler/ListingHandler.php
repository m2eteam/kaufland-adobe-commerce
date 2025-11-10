<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\InstallHandler;

use M2E\Core\Model\ResourceModel\Setup;
use M2E\Kaufland\Helper\Module\Database\Tables as TablesHelper;
use M2E\Kaufland\Model\ResourceModel\Listing as ListingResource;
use M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category as CategoryResource;
use M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group as CategoryGroupResource;
use M2E\Kaufland\Model\ResourceModel\Listing\Wizard as ListingWizardResource;
use M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product as ListingWizardProductResource;
use M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Step as ListingStepResource;
use Magento\Framework\DB\Ddl\Table;

class ListingHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Kaufland\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installListingTable($setup);
        $this->installListingWizardTable($setup);
        $this->installListingWizardStepTable($setup);
        $this->installListingWizardProductTable($setup);
        $this->installListingAutoCategoryTable($setup);
        $this->installListingAutoCategoryGroupTable($setup);
    }

    private function installListingTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_LISTING);

        $listingTable = $setup->getConnection()->newTable($tableName);

        $listingTable
            ->addColumn(
                ListingResource::COLUMN_ID,
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
                ListingResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingResource::COLUMN_STOREFRONT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ListingResource::COLUMN_STORE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingResource::COLUMN_CONDITION_VALUE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ListingResource::COLUMN_SKU_SETTINGS,
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_TEMPLATE_SHIPPING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_AUTO_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => 0]
            )
            ->addColumn(
                ListingResource::COLUMN_AUTO_GLOBAL_ADDING_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => 0]
            )
            ->addColumn(
                ListingResource::COLUMN_AUTO_GLOBAL_ADDING_ADD_NOT_VISIBLE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => 1]
            )
            ->addColumn(
                ListingResource::COLUMN_AUTO_GLOBAL_ADDING_TEMPLATE_CATEGORY_ID,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_AUTO_WEBSITE_ADDING_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => 0]
            )
            ->addColumn(
                ListingResource::COLUMN_AUTO_WEBSITE_ADDING_ADD_NOT_VISIBLE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => 0]
            )
            ->addColumn(
                ListingResource::COLUMN_AUTO_WEBSITE_ADDING_TEMPLATE_CATEGORY_ID,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_AUTO_WEBSITE_DELETING_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => 0]
            )
            ->addColumn(
                ListingResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('account_id', ListingResource::COLUMN_ACCOUNT_ID)
            ->addIndex('storefront_id', ListingResource::COLUMN_STOREFRONT_ID)
            ->addIndex('store_id', ListingResource::COLUMN_STORE_ID)
            ->addIndex('title', ListingResource::COLUMN_TITLE)
            ->addIndex('template_selling_format_id', ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID)
            ->addIndex('template_synchronization_id', ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($listingTable);
    }

    private function installListingWizardTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_WIZARD);

        $listingWizardTable = $setup->getConnection()->newTable($tableName);

        $listingWizardTable
            ->addColumn(
                ListingWizardResource::COLUMN_ID,
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
                ListingWizardResource::COLUMN_LISTING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ListingWizardResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                50,
            )
            ->addColumn(
                ListingWizardResource::COLUMN_CURRENT_STEP_NICK,
                Table::TYPE_TEXT,
                150,
            )
            ->addColumn(
                ListingWizardResource::COLUMN_PRODUCT_COUNT_TOTAL,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                ListingWizardResource::COLUMN_IS_COMPLETED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                ListingWizardResource::COLUMN_PROCESS_START_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                ListingWizardResource::COLUMN_PROCESS_END_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                ListingWizardResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                ListingWizardResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addIndex('listing_id', ListingWizardResource::COLUMN_LISTING_ID)
            ->addIndex('is_completed', ListingWizardResource::COLUMN_IS_COMPLETED)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($listingWizardTable);
    }

    private function installListingWizardStepTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_WIZARD_STEP);

        $stepTable = $setup->getConnection()->newTable($tableName);

        $stepTable
            ->addColumn(
                ListingStepResource::COLUMN_ID,
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
                ListingStepResource::COLUMN_WIZARD_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ListingStepResource::COLUMN_NICK,
                Table::TYPE_TEXT,
                150,
            )
            ->addColumn(
                ListingStepResource::COLUMN_DATA,
                Table::TYPE_TEXT,
                Setup::LONG_COLUMN_SIZE,
                ['default' => null],
            )
            ->addColumn(
                ListingStepResource::COLUMN_IS_COMPLETED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                ListingStepResource::COLUMN_IS_SKIPPED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                ListingStepResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                ListingStepResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addIndex('wizard_id', ListingStepResource::COLUMN_WIZARD_ID)
            ->addIndex('is_completed', ListingStepResource::COLUMN_IS_COMPLETED)
            ->addIndex('is_skipped', ListingStepResource::COLUMN_IS_SKIPPED)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($stepTable);
    }

    private function installListingWizardProductTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_WIZARD_PRODUCT);

        $productTable = $setup->getConnection()->newTable($tableName);

        $productTable
            ->addColumn(
                ListingWizardProductResource::COLUMN_ID,
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
                ListingWizardProductResource::COLUMN_WIZARD_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_UNMANAGED_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_CATEGORY_TITLE,
                Table::TYPE_TEXT,
                255,
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_KAUFLAND_PRODUCT_ID,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_PRODUCT_ID_SEARCH_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_IS_PROCESSED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_IS_VALID_CATEGORY_ATTRIBUTES,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => null],
            )
            ->addColumn(
                ListingWizardProductResource::COLUMN_CATEGORY_ATTRIBUTES_ERRORS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true, 'default' => null]
            )
            ->addIndex('wizard_id', ListingWizardProductResource::COLUMN_WIZARD_ID)
            ->addIndex('category_id', ListingWizardProductResource::COLUMN_CATEGORY_ID)
            ->addIndex('kaufland_product_id', ListingWizardProductResource::COLUMN_KAUFLAND_PRODUCT_ID)
            ->addIndex('is_processed', ListingWizardProductResource::COLUMN_IS_PROCESSED)
            ->addIndex(
                'wizard_id_magento_product_id',
                [
                    ListingWizardProductResource::COLUMN_WIZARD_ID,
                    ListingWizardProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                ],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE],
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($productTable);
    }

    private function installListingAutoCategoryTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $listingAutoCategoryTable = $setup
            ->getConnection()
            ->newTable(
                $this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_AUTO_CATEGORY)
            );

        $listingAutoCategoryTable
            ->addColumn(
                CategoryResource::COLUMN_ID,
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
                CategoryResource::COLUMN_GROUP_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                CategoryResource::COLUMN_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                CategoryResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                CategoryResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($listingAutoCategoryTable);
    }

    private function installListingAutoCategoryGroupTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $listingAutoCategoryGroupTable = $setup
            ->getConnection()
            ->newTable(
                $this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_AUTO_CATEGORY_GROUP)
            );

        $listingAutoCategoryGroupTable
            ->addColumn(
                CategoryGroupResource::COLUMN_ID,
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
                CategoryGroupResource::COLUMN_LISTING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                CategoryGroupResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                CategoryGroupResource::COLUMN_ADDING_MODE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                CategoryGroupResource::COLUMN_ADDING_ADD_NOT_VISIBLE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                CategoryGroupResource::COLUMN_ADDING_TEMPLATE_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                CategoryGroupResource::COLUMN_DELETING_MODE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                CategoryGroupResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                CategoryGroupResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($listingAutoCategoryGroupTable);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }
}
