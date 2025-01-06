<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Support;

class Index extends \M2E\Kaufland\Controller\Adminhtml\AbstractBase
{
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::help_center_m2e_support');
    }

    public function execute()
    {
        $this->addContent(
            $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Support::class)
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend((string)__('Contact Us'));

        return $this->getResult();
    }

    protected function initResultPage(): void
    {
        if ($this->resultPage !== null) {
            return;
        }

        parent::initResultPage();

        $this->getResultPage()->setActiveMenu(\M2E\Kaufland\Helper\View\Kaufland::MENU_ROOT_NODE_NICK);
    }
}
