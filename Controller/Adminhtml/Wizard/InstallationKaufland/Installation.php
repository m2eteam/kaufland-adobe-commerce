<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Wizard\InstallationKaufland;

class Installation extends \M2E\Kaufland\Controller\Adminhtml\Wizard\AbstractInstallation
{
    public function execute()
    {
        return $this->installationAction();
    }
}
