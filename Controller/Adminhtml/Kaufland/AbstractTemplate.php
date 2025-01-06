<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland;

abstract class AbstractTemplate extends AbstractMain
{
    protected \M2E\Kaufland\Model\Kaufland\Template\Manager $templateManager;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Template\Manager $templateManager
    ) {
        parent::__construct();
        $this->templateManager = $templateManager;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::configuration_templates');
    }
}
