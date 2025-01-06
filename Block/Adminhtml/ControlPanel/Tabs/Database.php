<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\ControlPanel\Tabs;

use M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractContainer;

class Database extends AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelDatabase');

        $this->_controller = 'adminhtml_controlPanel_tabs_database';

        $this->setTemplate('magento/grid/container/only_content.phtml');

        $this->removeButton('add');
    }
}
