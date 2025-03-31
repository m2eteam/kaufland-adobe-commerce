<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y25_m03;

class RemoveOldCronValues extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $coreConfig = $this->getConfigModifier(\M2E\Kaufland\Helper\Module::IDENTIFIER);
        $coreConfig->delete('/cron/', 'last_executed_task_group');
    }
}
