<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Order\Note;

class Popup extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('order/note.phtml');
    }
}
