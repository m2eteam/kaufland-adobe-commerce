<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml;

class Settings extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractContainer
{
    protected function _construct()
    {
        parent::_construct();

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->addButton('save', [
            'label' => __('Save'),
            'onclick' => 'KauflandSettingsObj.saveSettings()',
            'class' => 'primary',
        ]);
    }

    protected function _toHtml()
    {
        return parent::_toHtml() . '<div id="tabs_container"></div>';
    }
}
