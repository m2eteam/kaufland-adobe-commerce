<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m09;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Listing as ListingResource;
use M2E\Kaufland\Model\ResourceModel\Product as ProductResource;
use M2E\Kaufland\Model\ResourceModel\Listing\Other as OtherListingResource;
use M2E\Kaufland\Model\ResourceModel\ShippingGroup as ShippingGroupResource;
use M2E\Kaufland\Model\ResourceModel\Template\Synchronization as SynchronizationResource;
use M2E\Kaufland\Model\ResourceModel\Warehouse as WarehouseResource;

class FixTablesStructure extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{

    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING);

        $modifier->changeColumn(
            ListingResource::COLUMN_CONDITION_VALUE,
            'VARCHAR(255) NOT NULL',
            null,
            ListingResource::COLUMN_STORE_ID,
            false
        )
                 ->commit();

        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING_OTHER);

        $modifier->changeColumn(
            OtherListingResource::COLUMN_HANDLING_TIME,
            'SMALLINT UNSIGNED NOT NULL',
            null,
            OtherListingResource::COLUMN_TITLE,
            false
        );

        $modifier->changeColumn(
            OtherListingResource::COLUMN_WAREHOUSE_ID,
            'INT UNSIGNED',
            null,
            OtherListingResource::COLUMN_HANDLING_TIME,
            false
        );

        $modifier->changeColumn(
            OtherListingResource::COLUMN_SHIPPING_GROUP_ID,
            'INT UNSIGNED',
            null,
            OtherListingResource::COLUMN_WAREHOUSE_ID,
            false
        );

        $modifier->changeColumn(
            OtherListingResource::COLUMN_CONDITION,
            'VARCHAR(255) NOT NULL',
            null,
            OtherListingResource::COLUMN_SHIPPING_GROUP_ID,
            false
        );
        $modifier->commit();

        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->changeColumn(
            ProductResource::COLUMN_ONLINE_HANDLING_TIME,
            'SMALLINT UNSIGNED NOT NULL',
            0,
            ProductResource::COLUMN_ONLINE_QTY,
            false
        );

        $modifier->changeColumn(
            ProductResource::COLUMN_ONLINE_WAREHOUSE_ID,
            'INT UNSIGNED NOT NULL',
            null,
            ProductResource::COLUMN_ONLINE_HANDLING_TIME,
            false
        );

        $modifier->changeColumn(
            ProductResource::COLUMN_ONLINE_SHIPPING_GROUP_ID,
            'INT UNSIGNED NOT NULL',
            null,
            ProductResource::COLUMN_ONLINE_WAREHOUSE_ID,
            false
        );

        $modifier->changeColumn(
            ProductResource::COLUMN_ONLINE_CONDITION,
            'VARCHAR(255)',
            null,
            ProductResource::COLUMN_ONLINE_SHIPPING_GROUP_ID,
            false
        );

        $modifier->changeColumn(
            ProductResource::COLUMN_ONLINE_CATEGORIES_ATTRIBUTES_DATA,
            'LONGTEXT',
            null,
            ProductResource::COLUMN_ONLINE_CATEGORIES_DATA,
            false
        );

        $modifier->changeColumn(
            ProductResource::COLUMN_ONLINE_TITLE,
            'VARCHAR(255)',
            null,
            ProductResource::COLUMN_ONLINE_CATEGORIES_ATTRIBUTES_DATA,
            false
        );

        $modifier->changeColumn(
            ProductResource::COLUMN_ONLINE_DESCRIPTION,
            'VARCHAR(255)',
            null,
            ProductResource::COLUMN_ONLINE_TITLE,
            false
        );

        $modifier->changeColumn(
            ProductResource::COLUMN_ONLINE_IMAGE,
            'VARCHAR(255)',
            null,
            ProductResource::COLUMN_ONLINE_DESCRIPTION,
            false
        );

        $modifier->commit();

        $modifier = $this->createTableModifier(Tables::TABLE_NAME_SHIPPING_GROUP);

        $modifier->changeColumn(
            ShippingGroupResource::COLUMN_CURRENCY,
            'VARCHAR(50) NOT NULL',
            null,
            ShippingGroupResource::COLUMN_NAME,
            false
        );

        $modifier->changeColumn(
            ShippingGroupResource::COLUMN_TYPE,
            'VARCHAR(255) NOT NULL',
            null,
            ShippingGroupResource::COLUMN_NAME,
            false
        );

        $modifier->changeColumn(
            ShippingGroupResource::COLUMN_REGIONS,
            'VARCHAR(255) NOT NULL',
            null,
            ShippingGroupResource::COLUMN_IS_DEFAULT,
            false
        );

        $modifier->commit();

        $modifier = $this->createTableModifier(Tables::TABLE_NAME_WAREHOUSE);

        $modifier->changeColumn(
            WarehouseResource::COLUMN_TYPE,
            'VARCHAR(255) NOT NULL',
            null,
            WarehouseResource::COLUMN_IS_DEFAULT,
            false
        );

        $modifier->commit();

        $modifier = $this->createTableModifier(Tables::TABLE_NAME_TEMPLATE_SYNCHRONIZATION);

        $modifier->changeColumn(
            SynchronizationResource::COLUMN_REVISE_UPDATE_TITLE,
            'SMALLINT UNSIGNED NOT NULL',
            null,
            SynchronizationResource::COLUMN_REVISE_UPDATE_PRICE,
            false
        );

        $modifier->changeColumn(
            SynchronizationResource::COLUMN_REVISE_UPDATE_CATEGORIES,
            'SMALLINT UNSIGNED NOT NULL',
            null,
            SynchronizationResource::COLUMN_REVISE_UPDATE_TITLE,
            false
        );

        $modifier->changeColumn(
            SynchronizationResource::COLUMN_REVISE_UPDATE_IMAGES,
            'SMALLINT UNSIGNED NOT NULL',
            null,
            SynchronizationResource::COLUMN_REVISE_UPDATE_CATEGORIES,
            false
        );

        $modifier->changeColumn(
            SynchronizationResource::COLUMN_REVISE_UPDATE_DESCRIPTION,
            'SMALLINT UNSIGNED NOT NULL',
            null,
            SynchronizationResource::COLUMN_REVISE_UPDATE_IMAGES,
            false
        );

        $modifier->commit();
    }
}
