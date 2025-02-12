<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel\Database;

class UpdateTableCells extends AbstractTable
{
    public function execute()
    {
        $ids = $this->prepareIds();
        $cellsValues = $this->prepareCellsValuesArray();
        $modelInstance = $this->getTableModel();

        if (empty($ids) || empty($cellsValues)) {
            return;
        }

        $modelInstance->updateEntries($ids, $cellsValues);

        $this->afterTableAction($modelInstance->getTableName());
    }
}
