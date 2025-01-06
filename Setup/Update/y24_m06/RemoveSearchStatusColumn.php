<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m06;

use M2E\Kaufland\Helper\Module\Database\Tables;

class RemoveSearchStatusColumn extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);
        $modifier->dropColumn('product_id_search_status');
    }
}
