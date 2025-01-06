<?php

namespace M2E\Kaufland\Controller\Adminhtml\Order;

use M2E\Kaufland\Controller\Adminhtml\AbstractOrder;

class ProductMappingGrid extends AbstractOrder
{
    public function execute()
    {
        $grid = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Order\Item\Product\Mapping\Grid::class);
        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
