<?php

namespace M2E\Kaufland\Block\Adminhtml\Listing;

use M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractContainer;

/**
 * Class \M2E\Kaufland\Block\Adminhtml\Listing\Edit
 */
class Edit extends AbstractContainer
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_listing';

        parent::_construct();
    }
}
