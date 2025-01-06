<?php

namespace M2E\Kaufland\Controller\Adminhtml;

abstract class AbstractGeneral extends \M2E\Kaufland\Controller\Adminhtml\AbstractBase
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::main');
    }
}
