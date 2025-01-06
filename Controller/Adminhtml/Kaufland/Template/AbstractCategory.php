<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

abstract class AbstractCategory extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractMain
{
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::configuration_categories');
    }
}
