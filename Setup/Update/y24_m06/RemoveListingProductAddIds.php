<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m06;

use M2E\Kaufland\Helper\Module\Database\Tables;

class RemoveListingProductAddIds extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING);

        $modifier->dropColumn('product_add_ids')
                 ->commit();
    }
}
