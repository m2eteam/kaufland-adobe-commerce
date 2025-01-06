<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland;

abstract class AbstractMain extends \M2E\Kaufland\Controller\Adminhtml\AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::main');
    }

    protected function initResultPage()
    {
        if ($this->resultPage !== null) {
            return;
        }

        parent::initResultPage();

        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(\M2E\Kaufland\Helper\View\Kaufland::getTitle());

        if ($this->getLayoutType() !== self::LAYOUT_BLANK) {
            $this->getResultPage()->setActiveMenu(\M2E\Kaufland\Helper\View\Kaufland::MENU_ROOT_NODE_NICK);
        }
    }
}
