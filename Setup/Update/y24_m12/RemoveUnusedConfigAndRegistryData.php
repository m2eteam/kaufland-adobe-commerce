<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m12;

use M2E\Core\Helper\Module\Database\Tables as CoreTables;

class RemoveUnusedConfigAndRegistryData extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $configTable = $this->getFullTableName(CoreTables::TABLE_NAME_CONFIG);
        $registryTable = $this->getFullTableName(CoreTables::TABLE_NAME_REGISTRY);
        $connection = $this->getConnection();

        $connection->delete(
            $registryTable,
            [
                '`extension_name` = ?' => \M2E\Kaufland\Helper\Module::IDENTIFIER,
                '`key` = ?' => '/location/date_last_check/',
            ]
        );

        $connection->delete(
            $configTable,
            [
                '`extension_name` = ?' => \M2E\Kaufland\Helper\Module::IDENTIFIER,
                '`group` = ?' => '/location/',
                '`key` = ?' => 'domain',
            ]
        );

        $connection->delete(
            $configTable,
            [
                '`extension_name` = ?' => \M2E\Kaufland\Helper\Module::IDENTIFIER,
                '`group` = ?' => '/location/',
                '`key` = ?' => 'ip',
            ]
        );
    }
}
