<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup;

trait InstallHandlerTrait
{
    private \M2E\Kaufland\Helper\Module\Database\Tables $tablesHelper;
    private \M2E\Core\Model\Setup\Database\Modifier\ConfigFactory $modifierConfigFactory;

    public function __construct(
        \M2E\Kaufland\Helper\Module\Database\Tables $tablesHelper,
        \M2E\Core\Model\Setup\Database\Modifier\ConfigFactory $modifierConfigFactory
    ) {
        $this->tablesHelper = $tablesHelper;
        $this->modifierConfigFactory = $modifierConfigFactory;
    }

    private function getFullTableName(string $tableName): string
    {
        return $this->tablesHelper->getFullName($tableName);
    }
}
