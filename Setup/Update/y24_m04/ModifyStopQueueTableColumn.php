<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m04;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\StopQueue as StopQueueResource;

class ModifyStopQueueTableColumn extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_STOP_QUEUE);

        $modifier->renameColumn('additional_data', StopQueueResource::COLUMN_REQUEST_DATA);
        $modifier->dropColumn('component_mode');
    }
}
