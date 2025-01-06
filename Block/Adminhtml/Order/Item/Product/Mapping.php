<?php

namespace M2E\Kaufland\Block\Adminhtml\Order\Item\Product;

use M2E\Kaufland\Block\Adminhtml\Magento\AbstractContainer;

class Mapping extends AbstractContainer
{
    protected $_template = 'order/item/product/mapping.phtml';

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $mappingGrid = $this
            ->getLayout()
            ->createBlock(\M2E\Kaufland\Block\Adminhtml\Order\Item\Product\Mapping\Grid::class);

        $this->setChild('product_mapping_grid', $mappingGrid);

        $text = __('As M2E Kaufland was not able to find appropriate Product in Magento Catalog,
                     you are supposed to find and map it manually.');
        $note = __('Note:');
        $text2 = __('Magento Order can be only created when all Products of
                     Order are found in Magento Catalog.');

        $helpBlockHtml = $text . '<br/><br/><b>' . $note . ' ' . '</b>' . $text2;

        $helpBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Kaufland\Block\Adminhtml\HelpBlock::class)
            ->setData(['content' => $helpBlockHtml]);

        $this->setChild('product_mapping_help_block', $helpBlock);

        return parent::_beforeToHtml();
    }
}
