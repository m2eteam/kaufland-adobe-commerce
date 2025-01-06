<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Product\Add;

class SourceMode extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractContainer
{
    public const MODE_PRODUCT = 'product';
    public const MODE_CATEGORY = 'category';
    public const MODE_OTHER = 'other';

    public function _construct()
    {
        parent::_construct();

        $this->setId('kauflandListingSourceMode');
        $this->_controller = 'adminhtml_kaufland_listing_product_add';
        $this->_mode = 'sourceMode';

        $this->_headerText = __('Add Products');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        if (!$this->getRequest()->getParam('listing_creation', false)) {
            $url = $this->getUrl('*/kaufland_listing/view', [
                'id' => $this->getRequest()->getParam('id'),
            ]);
            $this->addButton('back', [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class' => 'back',
            ]);
        }

        // ---------------------------------------
        $url = $this->getUrl(
            '*/kaufland_listing_product_add/exitToListing',
            ['id' => $this->getRequest()->getParam('id')]
        );
        $confirm =
            '<strong>' . __('Are you sure?') . '</strong><br><br>'
            . __('All unsaved changes will be lost and you will be returned to the Listings grid.');
        $this->addButton(
            'exit_to_listing',
            [
                'label' => __('Cancel'),
                'onclick' => "confirmSetLocation('$confirm', '$url');",
                'class' => 'action-primary',
            ]
        );

        $url = $this->getUrl('*/*/*', ['_current' => true]);
        $this->addButton('next', [
            'label' => __('Continue'),
            'onclick' => 'CommonObj.submitForm(\'' . $url . '\');',
            'class' => 'action-primary forward',
        ]);
        // ---------------------------------------
    }
}
