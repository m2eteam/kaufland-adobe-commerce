<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template;

class Category extends \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('kauflandTemplateCategory');
        $this->_controller = 'adminhtml_kaufland_template_category';

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('save');
        $this->buttonList->remove('edit');
        $this->buttonList->remove('add');

        $this->buttonList->update('add', 'label', __('Add Category'));
        $this->buttonList->update('add', 'onclick', '');
    }

    protected function _prepareLayout()
    {
        $url = $this->getUrl('*/kaufland_category/update');
        $this->addButton('update', [
            'label' => __('Update Category Data'),
            'onclick' => 'setLocation(\'' . $url . '\')',
            'class' => 'action-primary',
            'button_class' => '',
        ]);

        return parent::_prepareLayout();
    }
}
