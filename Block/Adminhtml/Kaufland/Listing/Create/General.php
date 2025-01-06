<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Create;

class General extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('listingCreateGeneral');
        $this->_controller = 'adminhtml_Kaufland_listing_create';
        $this->_mode = 'general';

        $this->_headerText = __('Creating A New Listing');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->addButton(
            'next',
            [
                'label' => __('Next Step'),
                'class' => 'action-primary next_step_button forward',
            ]
        );
    }

    protected function _toHtml()
    {
        $breadcrumb = $this->getLayout()
                           ->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Create\Breadcrumb::class);
        $breadcrumb->setSelectedStep(1);

        $helpBlock = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\HelpBlock::class);
        $text = __(
            'It is necessary to select an Kaufland Account (existing or create a new one) as well as choose a Storefront that you
are going to sell Magento Products on.'
        );
        $text1 = __(
            'It is also important to specify a Store View in accordance with which Magento Attribute values will be used in the
Listing settings.'
        );
        $helpBlock->addData(
            [
                'content' =>
                    "<p>$text</p><br>
                    <p>$text1</p>"
                ,
                'style' => 'margin-top: 30px',
            ]
        );

        return
            $breadcrumb->_toHtml() .
            '<div id="progress_bar"></div>' .
            $helpBlock->toHtml() .
            '<div id="content_container">' . parent::_toHtml() . '</div>';
    }
}
