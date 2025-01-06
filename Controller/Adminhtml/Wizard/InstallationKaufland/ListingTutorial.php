<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Wizard\InstallationKaufland;

class ListingTutorial extends Installation
{
    public function execute()
    {
        $this->init();

        return $this->renderSimpleStep();
    }
}
