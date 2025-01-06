<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Product;

class Add extends \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    private \M2E\Core\Helper\Url $urlHelper;
    private string $sourceMode;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\Core\Helper\Url $urlHelper,
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->sourceMode = $this->getRequest()->getParam('source');

        $this->setId('kauflandListingProduct');
        $this->_controller = 'adminhtml_kaufland_listing_product_add_';
        $this->_controller .= $this->sourceMode;
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = __('Select Products');
        // ---------------------------------------
    }

    protected function _prepareLayout()
    {
        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('add');
        // ---------------------------------------

        $this->css->addFile('listing/autoAction.css');

        // ---------------------------------------

        if ((bool)$this->getRequest()->getParam('listing_creation', false)) {
            $url = $this->getUrl(
                '*/*/sourceMode',
                [
                    '_current' => true,
                    'listing_creation' => $this->getRequest()->getParam('listing_creation', 0),
                ]
            );
        } else {
            $url = $this->getUrl('*/kaufland_listing/view', [
                'id' => $this->getRequest()->getParam('id'),
            ]);

            if ($this->getRequest()->getParam('back')) {
                $url = $this->urlHelper->getBackUrl();
            }
        }

        $this->addButton('back', [
            'label' => __('Back'),
            'class' => 'back',
            'onclick' => 'setLocation(\'' . $url . '\')',
        ]);
        // ---------------------------------------

        if ($this->getRequest()->getParam('listing_creation')) {
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
        }

        $this->addButton('continue', [
            'label' => __('Continue'),
            'class' => 'action-primary forward',
            'onclick' => 'ListingProductAddObj.continue();',
        ]);
        // ---------------------------------------

        $this->jsTranslator->addTranslations([
            'Remove Category' => __('Remove Category'),
            'Add New Rule' => __('Add New Rule'),
            'Add/Edit Categories Rule' => __('Add/Edit Categories Rule'),
            'Start Configure' => __('Start Configure'),
        ]);

        $this->addGrid();

        return parent::_prepareLayout();
    }

    private function addGrid(): void
    {
        switch ($this->sourceMode) {
            case \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Product\Add\SourceMode::MODE_PRODUCT:
                $gridClass = \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Product\Add\Product\Grid::class;
                break;

            case \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Product\Add\SourceMode::MODE_CATEGORY:
                $gridClass = \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Product\Add\Category\Grid::class;
                break;

            default:
                throw new \M2E\Kaufland\Model\Exception\Logic(sprintf('Unknown source mode - %s', $this->sourceMode));
        }

        $this->addChild('grid', $gridClass);
    }

    protected function _toHtml()
    {
        return '<div id="add_products_progress_bar"></div>' .
            '<div id="add_products_container">' .
            parent::_toHtml() .
            '</div>';
    }
}
