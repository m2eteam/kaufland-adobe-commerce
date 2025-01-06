<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Wizard\InstallationKaufland;

use M2E\Kaufland\Controller\Adminhtml\Wizard\InstallationKaufland;

class ListingGeneral extends Installation
{
    public function execute()
    {
        $this->setWizardStatusCompleted();

        return $this->_redirect('*/kaufland_listing_create', ['step' => 2, 'wizard' => true]);
    }
}
