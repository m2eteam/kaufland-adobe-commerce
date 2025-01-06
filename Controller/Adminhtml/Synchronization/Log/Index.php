<?php

namespace M2E\Kaufland\Controller\Adminhtml\Synchronization\Log;

class Index extends \M2E\Kaufland\Controller\Adminhtml\Synchronization\AbstractLog
{
    public function execute()
    {
        $this->addContent(
            $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Synchronization\Log::class)
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Synchronization Logs'));

        return $this->getResult();
    }
}
