<?php

namespace M2E\Kaufland\Block\Adminhtml;

/**
 * @method void setTooltiped()
 * @method void setNoHide()
 * @method void setNoCollapse()
 */
class HelpBlock extends Magento\AbstractBlock
{
    protected $_template = 'M2E_Kaufland::help_block.phtml';

    /**
     * @return string
     */
    public function getId()
    {
        if (null === $this->getData('id') && $this->getContent()) {
            $this->setData('id', 'block_notice_' . crc32($this->getContent()));
        }

        return $this->getData('id');
    }

    protected function _toHtml()
    {
        if ($this->getContent()) {
            return parent::_toHtml();
        }

        return '';
    }
}
