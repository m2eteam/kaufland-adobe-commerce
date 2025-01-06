<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing;

use M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractContainer;

class ItemsByListing extends AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('KauflandListingItemsByListing');
        $this->_controller = 'adminhtml_kaufland_listing_itemsByListing';
        // ---------------------------------------
    }

    protected function _prepareLayout()
    {
        $url = $this->getUrl('*/kaufland_listing_create/index', ['step' => 1, 'clear' => 1]);
        $this->addButton('add', [
            'label' => __('Add Listing'),
            'onclick' => 'setLocation(\'' . $url . '\')',
            'class' => 'action-primary',
            'button_class' => '',
        ]);

        return parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->createBlock(Tabs::class);
        $tabsBlock->activateItemsByListingTab();
        $tabsBlockHtml = $tabsBlock->toHtml();

        return $tabsBlockHtml . parent::_toHtml();
    }
}
