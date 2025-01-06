<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Log\Order;

class Grid extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Log\AbstractOrder
{
    public function execute()
    {
        $response = $this->getLayout()
                         ->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Log\Order\Grid::class)
                         ->toHtml();
        $this->setAjaxContent($response);

        return $this->getResult();
    }
}
