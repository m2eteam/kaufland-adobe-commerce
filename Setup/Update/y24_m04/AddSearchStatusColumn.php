<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m04;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Product;
use M2E\Kaufland\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\DB\Ddl\Table;

class AddSearchStatusColumn extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            'product_id_search_status',
            Table::TYPE_SMALLINT,
            0,
            ProductResource::COLUMN_KAUFLAND_PRODUCT_ID,
        );
    }
}
