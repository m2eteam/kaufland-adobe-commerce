<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m05;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\Template\Synchronization;

class UpdateSynchronizationTemplates extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $synchronizationTable = $this->getFullTableName(Tables::TABLE_NAME_TEMPLATE_SYNCHRONIZATION);
        $this->getConnection()->update(
            $synchronizationTable,
            [
                'list_mode' => 1,
                'list_status_enabled' => 1,
                'list_is_in_stock' => 1,
                'list_qty_calculated' => Synchronization::QTY_MODE_YES,
                'list_qty_calculated_value' => '1',
                'list_advanced_rules_mode' => 0,
                'list_advanced_rules_filters' => null,
            ]
        );
    }
}
