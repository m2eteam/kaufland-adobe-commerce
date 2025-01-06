<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel\Database;

class ShowOperationHistoryExecutionTreeDown extends AbstractTable
{
    private \M2E\Kaufland\Model\OperationHistoryFactory $operationHistoryFactory;
    private \M2E\Kaufland\Model\OperationHistory\Repository $repository;

    public function __construct(
        \M2E\Kaufland\Model\OperationHistoryFactory $operationHistoryFactory,
        \M2E\Kaufland\Model\OperationHistory\Repository $repository,
        \M2E\Kaufland\Helper\Module $moduleHelper,
        \M2E\Kaufland\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory,
        \M2E\Kaufland\Model\Module $module
    ) {
        parent::__construct($moduleHelper, $databaseTableFactory, $module);
        $this->operationHistoryFactory = $operationHistoryFactory;
        $this->repository = $repository;
    }

    public function execute()
    {
        $operationHistoryId = $this->getRequest()->getParam('operation_history_id');
        if (empty($operationHistoryId)) {
            $this->getMessageManager()->addErrorMessage("Operation history ID is not presented.");

            return $this->redirectToTablePage(
                \M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_OPERATION_HISTORY,
            );
        }

        $history = $this->repository->get((int)$operationHistoryId);
        $operationHistory = $this->operationHistoryFactory->create()
                                                          ->setObject($history);

        while ($parentId = $operationHistory->getObject()->getData('parent_id')) {
            $object = $operationHistory->load($parentId);
            $operationHistory->setObject($object);
        }

        $this->getResponse()->setBody(
            '<pre>' . $operationHistory->getExecutionTreeDownInfo() . '</pre>',
        );
    }
}
