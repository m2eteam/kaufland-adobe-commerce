<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

class NewAction extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractTemplate
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD);
        $resultForward->forward('edit');

        return $resultForward;
    }
}
