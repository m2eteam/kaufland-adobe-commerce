<?php

namespace M2E\Kaufland\Block\Adminhtml;

use M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock;

class PageHelpLink extends AbstractBlock
{
    /** @var string */
    protected $_template = 'page_help_link.phtml';

    protected function _toHtml()
    {
        if ($this->getPageHelpLink() === null) {
            return '';
        }

        return parent::_toHtml();
    }
}
