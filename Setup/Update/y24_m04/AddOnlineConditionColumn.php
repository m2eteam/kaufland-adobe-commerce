<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m04;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\DB\Ddl\Table;

class AddOnlineConditionColumn extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_CONDITION,
            Table::TYPE_TEXT,
            null,
            ProductResource::COLUMN_ONLINE_SHIPPING_GROUP_ID,
        );
    }
}