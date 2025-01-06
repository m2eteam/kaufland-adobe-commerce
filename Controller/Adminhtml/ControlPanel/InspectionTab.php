<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel;

use M2E\Kaufland\Helper\Module;
use Magento\Backend\App\Action;

class InspectionTab extends AbstractMain
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\ControlPanel\Tabs\Inspection::class,
            ''
        );
        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
