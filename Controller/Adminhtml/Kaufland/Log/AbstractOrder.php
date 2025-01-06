<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Log;

abstract class AbstractOrder extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::sales_logs');
    }
}
