<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Wizard\InstallationKaufland;

use M2E\Kaufland\Controller\Adminhtml\Wizard\InstallationKaufland;

class SetStatus extends Installation
{
    public function execute()
    {
        return $this->setStatusAction();
    }
}
