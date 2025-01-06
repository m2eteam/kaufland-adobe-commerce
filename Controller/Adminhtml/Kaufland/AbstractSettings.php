<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland;

abstract class AbstractSettings extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::configuration_settings');
    }
}
