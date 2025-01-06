<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Order;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractOrder;

class Grid extends AbstractOrder
{
    public function execute()
    {
        /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Order\Grid $grid */
        $grid = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Order\Grid::class);

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
