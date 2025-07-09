<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y25_m06;

use M2E\Kaufland\Helper\Module\Database\Tables;

class RemoveReferencesOfPolicyFromProduct extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);
        $modifier
            ->dropColumn('template_selling_format_mode', true, false)
            ->dropColumn('template_selling_format_id', true, false)
            ->dropColumn('template_synchronization_mode', true, false)
            ->dropColumn('template_synchronization_id', true, false);

        $modifier->commit();
    }
}
