<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel\Cron;

class Run extends \M2E\Kaufland\Controller\Adminhtml\ControlPanel\AbstractMain
{
    private \M2E\Kaufland\Model\Cron\Runner\Developer $cronRunner;

    public function __construct(
        \M2E\Kaufland\Model\Cron\Runner\Developer $cronRunner,
        \M2E\Kaufland\Model\Module $module
    ) {
        parent::__construct($module);
        $this->cronRunner = $cronRunner;
    }

    public function execute(): void
    {
        $taskCode = $this->getRequest()->getParam('task_code');

        if (!empty($taskCode)) {
            $this->cronRunner->setAllowedTasks([$taskCode]);
        }

        $this->cronRunner->process();

        $this->getResponse()->setBody('<pre>' . $this->cronRunner->getOperationHistory()->getFullDataInfo() . '</pre>');
    }
}
