<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\ControlPanel\Tabs;

use M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock;

class Inspection extends AbstractBlock
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelInspection');
        $this->setTemplate('control_panel/tabs/inspection.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->setChild(
            'inspections',
            $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\ControlPanel\Inspection\Grid::class)
        );

        return parent::_beforeToHtml();
    }
}
