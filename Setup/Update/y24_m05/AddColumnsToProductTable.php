<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m05;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\DB\Ddl\Table;

class AddColumnsToProductTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            ProductResource::COLUMN_IS_KAUFLAND_PRODUCT_CREATOR,
            Table::TYPE_BOOLEAN,
            0,
            ProductResource::COLUMN_KAUFLAND_PRODUCT_ID,
        );
        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_TITLE,
            Table::TYPE_TEXT,
            null,
            ProductResource::COLUMN_ONLINE_CATEGORIES_DATA
        );
        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_DESCRIPTION,
            Table::TYPE_TEXT,
            null,
            ProductResource::COLUMN_ONLINE_TITLE
        );
        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_IMAGE,
            Table::TYPE_TEXT,
            null,
            ProductResource::COLUMN_ONLINE_DESCRIPTION
        );
    }
}
