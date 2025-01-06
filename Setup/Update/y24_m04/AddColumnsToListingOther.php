<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m04;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Listing\Other as OtherListingResource;
use Magento\Framework\DB\Ddl\Table;

class AddColumnsToListingOther extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING_OTHER);

        $modifier->addColumn(
            OtherListingResource::COLUMN_HANDLING_TIME,
            Table::TYPE_SMALLINT,
            0,
            OtherListingResource::COLUMN_TITLE,
        );

        $modifier->addColumn(
            OtherListingResource::COLUMN_WAREHOUSE_ID,
            Table::TYPE_INTEGER,
            0,
            OtherListingResource::COLUMN_HANDLING_TIME,
        );

        $modifier->addColumn(
            OtherListingResource::COLUMN_SHIPPING_GROUP_ID,
            Table::TYPE_INTEGER,
            0,
            OtherListingResource::COLUMN_WAREHOUSE_ID,
        );

        $modifier->addColumn(
            OtherListingResource::COLUMN_CONDITION,
            Table::TYPE_TEXT,
            null,
            OtherListingResource::COLUMN_SHIPPING_GROUP_ID,
        );
    }
}
