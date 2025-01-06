<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\ControlPanel\Tabs;

use M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock;

class ToolsModule extends AbstractBlock
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelToolsModule');

        $this->setTemplate('control_panel/tabs/tools_module.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->setChild(
            'tabs',
            $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\ControlPanel\Tabs\ToolsModule\Tabs::class)
        );

        return parent::_beforeToHtml();
    }
}
