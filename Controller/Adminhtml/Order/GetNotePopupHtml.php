<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Order;

use M2E\Kaufland\Controller\Adminhtml\AbstractOrder;

class GetNotePopupHtml extends AbstractOrder
{
    public function execute()
    {
        $grid = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Order\Note\Popup::class);
        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
