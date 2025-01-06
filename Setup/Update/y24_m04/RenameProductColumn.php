<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m04;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Product as ProductResource;

class RenameProductColumn extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->renameColumn('product_id', ProductResource::COLUMN_KAUFLAND_PRODUCT_ID);
        $modifier->renameColumn('online_main_category', ProductResource::COLUMN_ONLINE_CATEGORY_ID);
    }
}
