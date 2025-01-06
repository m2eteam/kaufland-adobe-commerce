<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Create;

use M2E\Kaufland\Model\Listing;

class Templates extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractContainer
{
    /** @var \M2E\Kaufland\Helper\Data\Session */
    private $sessionDataHelper;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\Kaufland\Helper\Data\Session $sessionDataHelper,
        array $data = []
    ) {
        $this->sessionDataHelper = $sessionDataHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('KauflandListingCreateTemplates');
        $this->_controller = 'adminhtml_Kaufland_listing_create';
        $this->_mode = 'templates';

        $this->_headerText = __('Creating A New Listing');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl(
            '*/kaufland_listing_create/index',
            ['_current' => true, 'step' => 1]
        );
        $this->addButton(
            'back',
            [
                'label' => __('Previous Step'),
                'onclick' => 'CommonObj.backClick(\'' . $url . '\')',
                'class' => 'back',
            ]
        );

        $nextStepBtnText = 'Next Step';

        $sessionData = $this->sessionDataHelper->getValue(
            Listing::CREATE_LISTING_SESSION_DATA
        );
        if (
            isset($sessionData['creation_mode']) && $sessionData['creation_mode'] ===
            \M2E\Kaufland\Helper\View::LISTING_CREATION_MODE_LISTING_ONLY
        ) {
            $nextStepBtnText = 'Complete';
        }

        $url = $this->getUrl(
            '*/kaufland_listing_create/index',
            ['_current' => true]
        );

        $this->addButton(
            'save',
            [
                'label' => __($nextStepBtnText),
                'onclick' => 'CommonObj.saveClick(\'' . $url . '\')',
                'class' => 'action-primary forward',
            ]
        );
    }

    protected function _toHtml()
    {
        $breadcrumb = $this->getLayout()
                           ->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Create\Breadcrumb::class);
        $breadcrumb->setSelectedStep(2);

        $helpBlock = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\HelpBlock::class);

        $helpBlock->addData(
            [
                'content' => __(
                    '<p>In this Section, you set the shipping methods you offer, and whether you accept
                    returns. For that, select <b>Shipping</b>, and <b>Return</b> Policies for the Listing.</p>
                    <p>Also, you can choose the right listing format, provide a competitive price for your Items, set the detailed
                    description for products to attract more buyers. For that, select <b>Selling</b> and <b>Description</b>
                    Policies for the Listing.</p>
                    <p>You can set the preferences on how to synchronize your Items with Magento Catalog data. The rules can be defined in
                    <b>Synchronization</b> policy.</p>
                    <p>More details in <a href="%url" target="_blank">our documentation</a>.</p>',
                    ['url' => 'https://docs-m2.m2epro.com/m2e-kaufland-policies'],
                ),
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
