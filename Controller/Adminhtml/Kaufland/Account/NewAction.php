<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Account;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractAccount;

class NewAction extends AbstractAccount
{
    public function execute(): void
    {
        $this->_forward('edit', null, null, null);
    }
}
