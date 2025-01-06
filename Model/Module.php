<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model;

use M2E\Kaufland\Helper\Module\Database\Tables as ModuleTablesHelper;

class Module implements \M2E\Core\Model\ModuleInterface
{
    private bool $areImportantTablesExist;
    private \M2E\Core\Model\Module\Adapter $moduleAdapter;
    private \M2E\Kaufland\Model\Registry\Manager $registryManager;
    private \M2E\Core\Model\Module\AdapterFactory $moduleAdapterFactory;
    /** @var \M2E\Kaufland\Model\Config\Manager */
    private Config\Manager $configManager;
    private \M2E\Kaufland\Helper\View\Kaufland $viewHelper;
    private \M2E\Core\Helper\Module\Database\Structure $moduleDatabaseHelper;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \M2E\Core\Model\Module $coreModule;

    public function __construct(
        \M2E\Core\Model\Module\AdapterFactory $moduleAdapterFactory,
        \M2E\Kaufland\Model\Registry\Manager $registryManager,
        \M2E\Kaufland\Model\Config\Manager $configManager,
        \M2E\Kaufland\Helper\View\Kaufland $viewHelper,
        \M2E\Core\Helper\Module\Database\Structure $moduleDatabaseHelper,
        \M2E\Core\Model\Module $coreModule,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->registryManager = $registryManager;
        $this->moduleAdapterFactory = $moduleAdapterFactory;
        $this->configManager = $configManager;
        $this->viewHelper = $viewHelper;
        $this->moduleDatabaseHelper = $moduleDatabaseHelper;
        $this->resourceConnection = $resourceConnection;
        $this->coreModule = $coreModule;
    }

    public function getName(): string
    {
        return 'kaufland-m2';
    }

    public function getPublicVersion(): string
    {
        return $this->getAdapter()->getPublicVersion();
    }

    public function getSetupVersion(): string
    {
        return $this->getAdapter()->getSetupVersion();
    }

    public function getSchemaVersion(): string
    {
        return $this->getAdapter()->getSchemaVersion();
    }

    public function getDataVersion(): string
    {
        return $this->getAdapter()->getDataVersion();
    }

    public function hasLatestVersion(): bool
    {
        return $this->getAdapter()->hasLatestVersion();
    }

    public function setLatestVersion(string $version): void
    {
        $this->getAdapter()->setLatestVersion($version);
    }

    public function getLatestVersion(): ?string
    {
        return $this->getAdapter()->getLatestVersion();
    }

    public function isDisabled(): bool
    {
        return $this->getAdapter()->isDisabled();
    }

    public function disable(): void
    {
        $this->getAdapter()->disable();
    }

    public function enable(): void
    {
        $this->getAdapter()->enable();
    }

    public function isReadyToWork(): bool
    {
        return $this->coreModule->isReadyToWork()
            && $this->areImportantTablesExist()
            && $this->viewHelper->isInstallationWizardFinished();
    }

    public function areImportantTablesExist(): bool
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->areImportantTablesExist)) {
            return $this->areImportantTablesExist;
        }

        $result = true;
        foreach ([ModuleTablesHelper::TABLE_NAME_WIZARD] as $table) {
            $tableName = $this->moduleDatabaseHelper->getTableNameWithPrefix($table);
            if (!$this->resourceConnection->getConnection()->isTableExists($tableName)) {
                $result = false;
                break;
            }
        }

        return $this->areImportantTablesExist = $result;
    }

    // ----------------------------------------

    public function getAdapter(): \M2E\Core\Model\Module\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->moduleAdapter)) {
            $this->moduleAdapter = $this->moduleAdapterFactory->create(
                \M2E\Kaufland\Helper\Module::IDENTIFIER,
                $this->registryManager->getAdapter(),
                $this->configManager->getAdapter()
            );
        }

        return $this->moduleAdapter;
    }
}
