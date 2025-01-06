<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel\Inspection;

use M2E\Kaufland\Controller\Adminhtml\ControlPanel\AbstractMain;

class ChangeMaintenanceMode extends AbstractMain
{
    private \M2E\Kaufland\Helper\View\ControlPanel $controlPanelHelper;
    private \M2E\Kaufland\Helper\Module\Maintenance $maintenanceHelper;

    public function __construct(
        \M2E\Kaufland\Model\Module $module,
        \M2E\Kaufland\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\Kaufland\Helper\Module\Maintenance $maintenanceHelper
    ) {
        parent::__construct($module);
        $this->controlPanelHelper = $controlPanelHelper;
        $this->maintenanceHelper = $maintenanceHelper;
    }

    public function execute()
    {
        if ($this->maintenanceHelper->isEnabled()) {
            $this->maintenanceHelper->disable();
        } else {
            $this->maintenanceHelper->enable();
        }

        $this->messageManager->addSuccess(__('Changed.'));

        return $this->_redirect($this->controlPanelHelper->getPageUrl());
    }
}
