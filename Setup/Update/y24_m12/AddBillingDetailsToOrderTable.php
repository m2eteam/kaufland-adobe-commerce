<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m12;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Order as OrderResource;

class AddBillingDetailsToOrderTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ORDER);

        $modifier->addColumn(
            OrderResource::COLUMN_BILLING_DETAILS,
            'LONGTEXT',
            null,
            null,
            false,
            false
        );

        $modifier->commit();
    }
}
