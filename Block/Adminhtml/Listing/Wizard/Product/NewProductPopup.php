<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Product;

class NewProductPopup extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock
{
    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('searchEanNewProductPopup');
        // ---------------------------------------

        $this->setTemplate('listing/wizard/product_search_channel_id_popup.phtml');
    }
}
