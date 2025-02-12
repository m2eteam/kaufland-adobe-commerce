<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel\Database;

class ShowOperationHistoryExecutionTreeUp extends AbstractTable
{
    private \M2E\Kaufland\Model\OperationHistory\Repository $repository;

    public function __construct(
        \M2E\Kaufland\Model\OperationHistory\Repository $repository,
        \M2E\Kaufland\Helper\Module $moduleHelper,
        \M2E\Core\Model\ControlPanel\Database\TableModelFactory $tableModelFactory
    ) {
        parent::__construct($moduleHelper, $tableModelFactory);
        $this->repository = $repository;
    }

    public function execute()
    {
        $operationHistoryId = $this->getRequest()->getParam('operation_history_id');
        if (empty($operationHistoryId)) {
            $this->getMessageManager()->addErrorMessage('Operation history ID is not presented.');

            $this->redirectToTablePage(
                \M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_OPERATION_HISTORY
            );

            return;
        }

        $operationHistory = $this->repository->get((int)$operationHistoryId);

        $this->getResponse()->setBody(
            '<pre>' . $operationHistory->getExecutionTreeUpInfo() . '</pre>',
        );
    }
}
