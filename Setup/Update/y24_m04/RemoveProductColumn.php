<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m04;

use M2E\Kaufland\Helper\Module\Database\Tables;

class RemoveProductColumn extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->dropColumn('online_sku_value');
        $modifier->dropColumn('online_sku');
        $modifier->dropColumn('online_description');
        $modifier->dropColumn('online_title');
        $modifier->dropColumn('online_brand_id');
        $modifier->dropColumn('online_brand_name');
    }
}
