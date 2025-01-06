<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m03;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Listing as Listing;
use Magento\Framework\DB\Ddl\Table;

class AddColumsToListing extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING);

        $modifier->addColumn(
            Listing::COLUMN_CONDITION_VALUE,
            Table::TYPE_TEXT,
            null,
            Listing::COLUMN_STORE_ID,
        );

        $modifier->addColumn(
            Listing::COLUMN_TEMPLATE_SHIPPING_ID,
            Table::TYPE_INTEGER,
            null,
            Listing::COLUMN_TEMPLATE_SYNCHRONIZATION_ID,
        );
    }
}
