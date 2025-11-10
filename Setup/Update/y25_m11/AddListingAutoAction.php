<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y25_m11;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category as CategoryResource;
use M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group as CategoryGroupResource;
use Magento\Framework\DB\Ddl\Table;

class AddListingAutoAction extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createListingAutoCategoryTable();
        $this->createListingAutoCategoryGroupTable();
        $this->modifyListingTable();
    }

    private function createListingAutoCategoryTable(): void
    {
        $listingAutoCategoryTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_LISTING_AUTO_CATEGORY));

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

        $this->getConnection()->createTable($listingAutoCategoryTable);
    }

    private function createListingAutoCategoryGroupTable(): void
    {
        $listingAutoCategoryGroupTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_LISTING_AUTO_CATEGORY_GROUP));

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

        $this->getConnection()->createTable($listingAutoCategoryGroupTable);
    }

    private function modifyListingTable(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING);

        $modifier->addColumn(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_MODE,
            'SMALLINT UNSIGNED',
            0,
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_ADDITIONAL_DATA,
            false,
            false
        );

        //region GLOBAL
        $modifier->addColumn(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_GLOBAL_ADDING_MODE,
            'SMALLINT UNSIGNED',
            0,
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_MODE,
            false,
            false
        );
        $modifier->addColumn(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_GLOBAL_ADDING_ADD_NOT_VISIBLE,
            'SMALLINT UNSIGNED',
            1,
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_GLOBAL_ADDING_MODE,
            false,
            false
        );
        $modifier->addColumn(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_GLOBAL_ADDING_TEMPLATE_CATEGORY_ID,
            'SMALLINT UNSIGNED',
            null,
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_GLOBAL_ADDING_ADD_NOT_VISIBLE,
            false,
            false
        );
        //endregion

        //region WEBSITE
        $modifier->addColumn(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_WEBSITE_ADDING_MODE,
            'SMALLINT UNSIGNED',
            0,
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_GLOBAL_ADDING_TEMPLATE_CATEGORY_ID,
            false,
            false
        );
        $modifier->addColumn(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_WEBSITE_ADDING_ADD_NOT_VISIBLE,
            'SMALLINT UNSIGNED',
            0,
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_WEBSITE_ADDING_MODE,
            false,
            false
        );
        $modifier->addColumn(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_WEBSITE_ADDING_TEMPLATE_CATEGORY_ID,
            'SMALLINT UNSIGNED',
            null,
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_WEBSITE_ADDING_ADD_NOT_VISIBLE,
            false,
            false
        );
        $modifier->addColumn(
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_WEBSITE_DELETING_MODE,
            'SMALLINT UNSIGNED',
            0,
            \M2E\Kaufland\Model\ResourceModel\Listing::COLUMN_AUTO_WEBSITE_ADDING_TEMPLATE_CATEGORY_ID,
            false,
            false
        );
        //endregion

        $modifier->commit();
    }
}
