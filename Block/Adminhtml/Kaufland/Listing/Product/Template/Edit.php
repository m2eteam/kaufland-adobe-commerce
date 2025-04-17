<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Product\Template;

class Edit extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractContainer
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('kauflandListingProductTemplatePolicy');
        $this->_controller = 'adminhtml_kaufland_listing_product_template';
        $this->_mode = 'Edit';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    /**
     * @return string
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\HelpBlock::class);
        $helpBlock->addData(
            [
                'content' => __(
                    '<p>You may edit Policies assigned to your Listing or create new ones. The changes you make are automatically
                    applied to all %extension_title Listings that use this Policy.</p>
                    <p>Find more details on configuring Policies in the <a href="%url" target="_blank">documentation</a>.</p>',
                    [
                        'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
                        'url' => 'https://docs-m2.m2epro.com/m2e-kaufland-policies',
                    ],
                ),
                'style' => 'margin-top: 30px',
            ]
        );

        return $helpBlock->toHtml() . '<div id="content_container">' . parent::_toHtml() . '</div>';
    }
}
