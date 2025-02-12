<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ControlPanel;

class DatabaseRegistry implements \M2E\Core\Model\ControlPanel\Database\RegistryInterface
{
    public function getExtensionModuleName(): string
    {
        return \M2E\Kaufland\Model\ControlPanel\Extension::NAME;
    }

    public function getAllTables(): array
    {
        return \M2E\Kaufland\Helper\Module\Database\Tables::getAllTables();
    }

    public function isModuleTable(string $tableName): bool
    {
        return \M2E\Kaufland\Helper\Module\Database\Tables::isModuleTable($tableName);
    }

    public function getResourceModelClass(string $tableName): string
    {
        return \M2E\Kaufland\Helper\Module\Database\Tables::getTableResourceModel($tableName);
    }

    public function getModelClass(string $tableName): string
    {
        return \M2E\Kaufland\Helper\Module\Database\Tables::getTableModel($tableName);
    }
}
