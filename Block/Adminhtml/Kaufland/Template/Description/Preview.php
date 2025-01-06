<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Description;

use M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock;

class Preview extends AbstractBlock
{
    protected $_template = 'kaufland/template/description/preview.phtml';

    protected function _construct()
    {
        parent::_construct();

        $this->css->addFile('kaufland/template.css');
    }
}
