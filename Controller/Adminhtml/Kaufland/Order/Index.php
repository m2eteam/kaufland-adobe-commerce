<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Order;

class Index extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractOrder
{
    public function execute()
    {
        $this->init();
        $this->addContent($this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Order::class));
        $this->setPageHelpLink('https://docs-m2.m2epro.com/m2e-kaufland-orders');

        return $this->getResultPage();
    }
}
