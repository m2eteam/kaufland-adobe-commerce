<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Account;

class Edit extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractContainer
{
    protected function _construct()
    {
        parent::_construct();

        $this->_controller = 'adminhtml_kaufland_account';

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if ($this->getRequest()->getParam('close_on_save', false)) {
            if ($this->getRequest()->getParam('id')) {
                $this->addButton('save', [
                    'label' => __('Save And Close'),
                    'onclick' => 'KauflandAccountObj.saveAndClose()',
                    'class' => 'primary',
                ]);
            } else {
                $this->addButton('save_and_continue', [
                    'label' => __('Save And Continue Edit'),
                    'onclick' => 'KauflandAccountObj.saveAndEditClick(\'\',\'kauflandTabs\')',
                    'class' => 'primary',
                ]);
            }

            return;
        }

        $this->addButton('back', [
            'label' => __('Back'),
            'onclick' => 'KauflandAccountObj.backClick(\'' . $this->getUrl('*/kaufland_account/index') . '\')',
            'class' => 'back',
        ]);

        $saveButtonsProps = [];
        $this->addButton('delete', [
            'label' => __('Delete'),
            'onclick' => 'KauflandAccountObj.deleteClick()',
            'class' => 'delete kaufland_delete_button primary',
        ]);

        $saveButtonsProps['save'] = [
            'label' => __('Save And Back'),
            'onclick' => 'KauflandAccountObj.saveClick()',
            'class' => 'save primary',
        ];

        // ---------------------------------------
        $saveButtons = [
            'id' => 'save_and_continue',
            'label' => __('Save And Continue Edit'),
            'class' => 'add',
            'button_class' => '',
            'onclick' => 'KauflandAccountObj.saveAndEditClick(\'\', \'kauflandAccountEditTabs\')',
            'class_name' => \M2E\Kaufland\Block\Adminhtml\Magento\Button\SplitButton::class,
            'options' => $saveButtonsProps,
        ];

        $this->addButton('save_buttons', $saveButtons);
        // ---------------------------------------
    }

    protected function _toHtml(): string
    {
        $this->js->add(
            <<<JS
    require([
        'Kaufland/Kaufland/Account'
    ], function(){
        window.KauflandAccountObj = new KauflandAccount();
    });
JS,
        );

        return parent::_toHtml();
    }
}
