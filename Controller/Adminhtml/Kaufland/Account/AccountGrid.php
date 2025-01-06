<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Account;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractAccount;

class AccountGrid extends AbstractAccount
{
    public function execute()
    {
        /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Account\Grid $switcherBlock */
        $grid = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Account\Grid::class);

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
