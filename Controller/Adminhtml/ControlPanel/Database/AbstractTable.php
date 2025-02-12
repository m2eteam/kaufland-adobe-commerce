<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel\Database;

abstract class AbstractTable extends \M2E\Kaufland\Controller\Adminhtml\ControlPanel\AbstractMain
{
    private \M2E\Kaufland\Helper\Module $moduleHelper;
    private \M2E\Core\Model\ControlPanel\Database\TableModelFactory $tableModelFactory;

    public function __construct(
        \M2E\Kaufland\Helper\Module $moduleHelper,
        \M2E\Core\Model\ControlPanel\Database\TableModelFactory $tableModelFactory
    ) {
        parent::__construct();
        $this->moduleHelper = $moduleHelper;
        $this->tableModelFactory = $tableModelFactory;
    }

    protected function getTableModel(): \M2E\Core\Model\ControlPanel\Database\TableModel
    {
        return $this->tableModelFactory->createFromRequest();
    }

    protected function prepareCellsValuesArray(): array
    {
        $cells = $this->getRequest()->getParam('cells', []);
        if (is_string($cells)) {
            $cells = [$cells];
        }

        $bindArray = [];
        foreach ($cells as $columnName) {
            $columnValue = $this->getRequest()->getParam('value_' . $columnName);

            if ($columnValue === null) {
                continue;
            }

            if (strtolower($columnValue) === 'null') {
                $columnValue = null;
            }

            $bindArray[$columnName] = $columnValue;
        }

        return $bindArray;
    }

    protected function prepareIds(): array
    {
        $ids = explode(',', $this->getRequest()->getParam('ids'));

        return array_filter(array_map('intval', $ids));
    }

    protected function redirectToTablePage(string $tableName): void
    {
        $this->_redirect('*/*/manageTable', ['table' => $tableName]);
    }

    protected function afterTableAction(string $tableName): void
    {
        if (
            strpos($tableName, 'config') !== false
            || strpos($tableName, 'wizard') !== false
        ) {
            $this->moduleHelper->clearCache();
        }
    }
}
