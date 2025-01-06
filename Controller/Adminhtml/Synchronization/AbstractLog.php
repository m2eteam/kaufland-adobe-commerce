<?php

namespace M2E\Kaufland\Controller\Adminhtml\Synchronization;

abstract class AbstractLog extends \M2E\Kaufland\Controller\Adminhtml\AbstractMain
{
    protected function initResultPage(): void
    {
        if ($this->resultPage !== null) {
            return;
        }

        parent::initResultPage();

        $this->getResultPage()->setActiveMenu($this->getMenuRootNodeNick());
    }

    protected function getMenuRootNodeNick(): string
    {
        return \M2E\Kaufland\Helper\View\Kaufland::MENU_ROOT_NODE_NICK;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::help_center_synchronization_log');
    }
}
