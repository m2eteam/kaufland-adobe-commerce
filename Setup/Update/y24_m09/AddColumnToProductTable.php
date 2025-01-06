<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m09;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\DB\Ddl\Table;

class AddColumnToProductTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            ProductResource::COLUMN_IS_INCOMPLETE,
            Table::TYPE_BOOLEAN,
            0,
            ProductResource::COLUMN_STATUS,
            false,
            false
        );

        $modifier->addColumn(
            ProductResource::COLUMN_CHANNEL_PRODUCT_EMPTY_ATTRIBUTES,
            Table::TYPE_TEXT,
            null,
            ProductResource::COLUMN_ADDITIONAL_DATA,
            false,
            false
        );

        $modifier->commit();
    }
}
