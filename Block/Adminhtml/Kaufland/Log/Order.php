<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Log;

class Order extends \M2E\Kaufland\Block\Adminhtml\Log\Order\AbstractContainer
{
    protected function _toHtml()
    {
        $url = 'https://docs-m2.m2epro.com/m2e-kaufland-logs-events';
        if ($this->getRequest()->getParam('magento_order_failed')) {
            $message = __(
                'This Log contains information about your recent Kaufland orders for which Magento orders were not created.<br/><br/>
                Find detailed info in <a href="%url%" target="_blank">the article</a>.',
                ['url' => $url]
            );
        } else {
            $message = __(
                'This Log contains information about Order processing.<br/><br/>
                Find detailed info in <a href="%url" target="_blank">the article</a>.',
                ['url' => $url]
            );
        }
        $helpBlock = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\HelpBlock::class)->setData([
            'content' => $message,
        ]);

        return $helpBlock->toHtml() . parent::_toHtml();
    }
}
