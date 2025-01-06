<?php

namespace M2E\Kaufland\Block\Adminhtml\General;

use M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractContainer;

/**
 * Class \M2E\Kaufland\Block\Adminhtml\General\CreateAttribute
 */
class CreateAttribute extends AbstractContainer
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_controller = 'adminhtml_general';
        $this->_mode = 'createAttribute';

        // Initialization block
        // ---------------------------------------
        $this->setId('generalCreateAttribute');
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------
    }

    //########################################
}
