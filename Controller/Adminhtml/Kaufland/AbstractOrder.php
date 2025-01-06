<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland;

abstract class AbstractOrder extends AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::sales_orders');
    }

    protected function init()
    {
        $this->addCss('order.css');
        $this->addCss('switcher.css');
        $this->addCss('kaufland/order/grid.css');

        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Sales'));
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Orders'));
    }
}
