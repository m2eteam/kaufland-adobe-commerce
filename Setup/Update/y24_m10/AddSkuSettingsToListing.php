<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m10;

use M2E\Core\Model\ResourceModel\Setup;
use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Listing as ListingResource;
use Magento\Framework\DB\Ddl\Table;

class AddSkuSettingsToListing extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING);

        $modifier->addColumn(
            ListingResource::COLUMN_SKU_SETTINGS,
            'LONGTEXT',
            null,
            ListingResource::COLUMN_CONDITION_VALUE,
            false,
            false
        );

        $modifier->commit();
    }
}
