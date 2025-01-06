<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel;

use M2E\Kaufland\Helper\Module;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;

class OverviewTab extends AbstractMain
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\ControlPanel\Tabs\Overview::class, '');
        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
