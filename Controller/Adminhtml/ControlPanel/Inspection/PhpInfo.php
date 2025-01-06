<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel\Inspection;

use M2E\Kaufland\Controller\Adminhtml\ControlPanel\AbstractMain;
use M2E\Kaufland\Helper\Module;
use Magento\Backend\App\Action;

class PhpInfo extends AbstractMain
{
    public function execute()
    {
        phpinfo();
    }
}
