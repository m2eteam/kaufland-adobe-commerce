<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland;

abstract class AbstractWizard extends \M2E\Kaufland\Controller\Adminhtml\AbstractWizard
{
    protected function initResultPage()
    {
        if ($this->resultPage !== null) {
            return;
        }

        parent::initResultPage();

        $this->getResultPage()->setActiveMenu($this->getMenuRootNodeNick());
    }

    protected function getMenuRootNodeNick()
    {
        return \M2E\Kaufland\Helper\View\Kaufland::MENU_ROOT_NODE_NICK;
    }

    protected function getMenuRootNodeLabel()
    {
        return \M2E\Kaufland\Helper\View\Kaufland::getMenuRootNodeLabel();
    }
}
