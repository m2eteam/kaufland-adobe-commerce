<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task\Product;

class InspectDirectChangesTask extends \M2E\Kaufland\Model\Cron\AbstractTask
{
    public const NICK = 'product/inspect_direct_changes';

    private \M2E\Kaufland\Model\Product\InspectDirectChanges $inspectDirectChanges;
    private \M2E\Kaufland\Model\Product\InspectDirectChanges\Config $config;

    public function __construct(
        \M2E\Kaufland\Model\Product\InspectDirectChanges\Config $config,
        \M2E\Kaufland\Model\Product\InspectDirectChanges $inspectDirectChanges,
        \M2E\Kaufland\Model\Cron\Manager $cronManager,
        \M2E\Kaufland\Model\Synchronization\LogService $syncLogger,
        \M2E\Core\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $modelFactory,
            $activeRecordFactory,
            $taskRepo,
            $resource
        );

        $this->config = $config;
        $this->inspectDirectChanges = $inspectDirectChanges;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    public function isPossibleToRun()
    {
        if (
            !$this->config->isEnableProductInspectorMode()
        ) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    protected function performActions(): void
    {
        $this->inspectDirectChanges->process();
    }
}
