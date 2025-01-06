<?php

namespace M2E\Kaufland\Controller\Adminhtml;

abstract class AbstractHealthStatus extends \M2E\Kaufland\Controller\Adminhtml\AbstractBase
{
    protected function getLayoutType(): string
    {
        return self::LAYOUT_TWO_COLUMNS;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::help_center_health_status');
    }

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
}
