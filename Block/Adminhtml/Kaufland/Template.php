<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland;

use M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractContainer;
use M2E\Kaufland\Model\Kaufland\Template\Manager;

/**
 * Class \M2E\Kaufland\Block\Adminhtml\Kaufland\Template
 */
class Template extends AbstractContainer
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setId('KauflandTemplate');
        $this->_controller = 'adminhtml_Kaufland_template';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    //########################################

    protected function _prepareLayout()
    {
        $content = __(
            '<p>This Page displays the list of the Policies you are currently using in your Listings.
            Policy is a combination of settings that can be used in different Listings.</p><br>
            <p>You can <strong>Delete</strong> a Policy only if it\'s not being used for Listing.</p>'
        );

        $this->appendHelpBlock(
            [
                'content' => $content,
            ]
        );

        $addButtonProps = [
            'id' => 'add_policy',
            'label' => __('Add Policy'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => \M2E\Kaufland\Block\Adminhtml\Magento\Button\DropDown::class,
            'options' => $this->_getAddTemplateButtonOptions(),
        ];
        $this->addButton('add', $addButtonProps);

        return parent::_prepareLayout();
    }

    //########################################

    protected function _getAddTemplateButtonOptions()
    {
        $data = [
            Manager::TEMPLATE_SELLING_FORMAT => [
                'label' => __('Selling'),
                'id' => 'selling',
                'onclick' => "setLocation('" . $this->getTemplateUrl(Manager::TEMPLATE_SELLING_FORMAT) . "')",
                'default' => false,
            ],
            Manager::TEMPLATE_SYNCHRONIZATION => [
                'label' => __('Synchronization'),
                'id' => 'synchronization',
                'onclick' => "setLocation('" . $this->getTemplateUrl(Manager::TEMPLATE_SYNCHRONIZATION) . "')",
                'default' => false,
            ],
            Manager::TEMPLATE_SHIPPING => [
                'label' => __('Shipping'),
                'id' => 'Shipping',
                'onclick' => "setLocation('" . $this->getTemplateUrl(Manager::TEMPLATE_SHIPPING) . "')",
                'default' => false,
            ],
            Manager::TEMPLATE_DESCRIPTION => [
                'label' => __('Description'),
                'id' => 'Shipping',
                'onclick' => "setLocation('" . $this->getTemplateUrl(Manager::TEMPLATE_DESCRIPTION) . "')",
                'default' => false,
            ],
        ];

        return $data;
    }

    protected function getTemplateUrl($nick)
    {
        return $this->getUrl('*/kaufland_template/new', ['nick' => $nick]);
    }

    //########################################
}
