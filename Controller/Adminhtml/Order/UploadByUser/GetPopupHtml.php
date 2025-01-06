<?php

namespace M2E\Kaufland\Controller\Adminhtml\Order\UploadByUser;

class GetPopupHtml extends \M2E\Kaufland\Controller\Adminhtml\AbstractOrder
{
    public function execute()
    {
        /** @var \M2E\Kaufland\Block\Adminhtml\Order\UploadByUser\Popup $block */
        $block = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Order\UploadByUser\Popup::class);
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
