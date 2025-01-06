<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m11;

use M2E\Core\Helper\Module\Database\Tables as CoreTables;

class RemoveServerHost extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $configTable = $this->getFullTableName(CoreTables::TABLE_NAME_CONFIG);
        $connection = $this->getConnection();

        $connection->delete(
            $configTable,
            [
                '`extension_name` = ?' => \M2E\Kaufland\Helper\Module::IDENTIFIER,
                '`group` = ?' => '/server/',
                '`key` = ?' => 'host',
            ]
        );
    }
}
