<?php

namespace M2E\Kaufland\Controller\Adminhtml\Order\UploadByUser;

class GetPopupGrid extends \M2E\Kaufland\Controller\Adminhtml\AbstractOrder
{
    public function execute()
    {
        /** @var \M2E\Kaufland\Block\Adminhtml\Order\UploadByUser\Grid $block */
        $block = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Order\UploadByUser\Grid::class);
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
