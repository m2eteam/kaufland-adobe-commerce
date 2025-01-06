<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland;

abstract class AbstractAccount extends AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::configuration_accounts');
    }
}
