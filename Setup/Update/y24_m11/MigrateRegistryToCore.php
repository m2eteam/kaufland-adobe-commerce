<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m11;

use M2E\Core\Helper\Module\Database\Tables as CoreTables;

class MigrateRegistryToCore extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $connection = $this->getConnection();
        $oldTable = $this->getFullTableName('m2e_kaufland_registry');
        $newTable = $this->getFullTableName(CoreTables::TABLE_NAME_REGISTRY);

        if (!$connection->isTableExists($oldTable)) {
            return;
        }

        $query = $connection->select()->from($oldTable)->query();

        while ($row = $query->fetch()) {
            $exists = $connection->fetchOne(
                $connection->select()
                           ->from($newTable, ['key'])
                           ->where('`key` = ?', $row['key'])
                           ->where('`extension_name` = ?', \M2E\Kaufland\Helper\Module::IDENTIFIER)
            );

            if (!$exists) {
                $connection->insert($newTable, [
                    'extension_name' => \M2E\Kaufland\Helper\Module::IDENTIFIER,
                    'key' => $row['key'],
                    'value' => $row['value'],
                    'update_date' => $row['update_date'],
                    'create_date' => $row['create_date'],
                ]);
            }
        }

        $this->getConnection()->dropTable($oldTable);
    }
}
