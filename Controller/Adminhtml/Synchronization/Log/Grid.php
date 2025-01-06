<?php

namespace M2E\Kaufland\Controller\Adminhtml\Synchronization\Log;

class Grid extends \M2E\Kaufland\Controller\Adminhtml\Synchronization\AbstractLog
{
    public function execute()
    {
        $this->setAjaxContent(
            $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Synchronization\Log\Grid::class)
        );

        return $this->getResult();
    }
}
