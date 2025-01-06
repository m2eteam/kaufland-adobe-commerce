<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml;

abstract class AbstractListing extends AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::listings');
    }
}
