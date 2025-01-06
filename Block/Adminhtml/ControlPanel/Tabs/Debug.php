<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\ControlPanel\Tabs;

use M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock;

class Debug extends AbstractBlock
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelDebug');
        $this->setTemplate('control_panel/tabs/debug.phtml');
    }
}
