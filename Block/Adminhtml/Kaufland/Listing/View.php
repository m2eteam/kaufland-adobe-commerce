<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing;

use M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View\Switcher;

class View extends \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    /** @var \M2E\Kaufland\Model\Listing */
    private $listing;
    /** @var \M2E\Kaufland\Helper\Data */
    private $dataHelper;
    private \M2E\Core\Helper\Url $urlHelper;
    private string $viewMode;

    public function __construct(
        \M2E\Kaufland\Model\Listing $listing,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\Kaufland\Helper\Data $dataHelper,
        \M2E\Core\Helper\Url $urlHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->urlHelper = $urlHelper;
        $this->listing = $listing;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        /** @var Switcher $viewModeSwitcher */
        $viewModeSwitcher = $this->getLayout()
                                 ->createBlock(Switcher::class);

        // Initialization block
        // ---------------------------------------
        $this->setId('kauflandListingView');
        $this->_controller = 'adminhtml_kaufland_listing_view_' . $viewModeSwitcher->getSelectedParam();
        $this->viewMode = $viewModeSwitcher->getSelectedParam();
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('add');
        // ---------------------------------------
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('listing/autoAction.css');
        $this->css->addFile('listing/view.css');

        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Listing::class)
        );
        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(
                \M2E\Kaufland\Block\Adminhtml\Log\Listing\Product\AbstractGrid::class
            )
        );

        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->appendHelpBlock(
                [
                    'content' => __(
                        '<p>M2E Kaufland Listing is a group of Magento Products sold on a certain Storefront
                    from a particular Account. M2E Kaufland has several options to display the content of
                    Listings referring to different data details. Each of the view options contains a
                    unique set of available Actions accessible in the Mass Actions drop-down.</p>'
                    ),
                ]
            );

            $this->setPageActionsBlock(
                'Kaufland_Listing_View_Switcher',
                'kaufland_listing_view_switcher'
            );
        }

        // ---------------------------------------
        $backUrl = $this->urlHelper->getBackUrl('*/kaufland_listing/index');

        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $backUrl . '\');',
                'class' => 'back',
            ]
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl(
            '*/kaufland_log_listing_product',
            [
                \M2E\Kaufland\Block\Adminhtml\Log\AbstractGrid::LISTING_ID_FIELD =>
                    $this->listing->getId(),
            ]
        );
        $this->addButton(
            'view_log',
            [
                'label' => __('Logs & Events'),
                'onclick' => 'window.open(\'' . $url . '\',\'_blank\')',
            ]
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->addButton(
            'edit_templates',
            [
                'label' => __('Edit Settings'),
                'onclick' => '',
                'class' => 'drop_down edit_default_settings_drop_down primary',
                'class_name' => \M2E\Kaufland\Block\Adminhtml\Magento\Button\DropDown::class,
                'options' => $this->getSettingsButtonDropDownItems(),
            ]
        );
        // ---------------------------------------

        $url = $this->getUrl(
            '*/listing_wizard/create',
            [
                'listing_id' => $this->listing->getId(),
                'type' => \M2E\Kaufland\Model\Listing\Wizard::TYPE_GENERAL,
            ]
        );

        $this->addButton(
            'listing_product_wizard',
            [
                'id' => 'listing_product_wizard',
                'label' => __('Add Products'),
                'class' => 'add primary',
                'onclick' => "setLocation('$url')",
            ]
        );

        // ---------------------------------------

        $this->addGrid();
        return parent::_prepareLayout();
    }

    private function addGrid(): void
    {
        switch ($this->viewMode) {
            case Switcher::VIEW_MODE_KAUFLAND:
                $gridClass = \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View\Kaufland\Grid::class;
                break;
            case Switcher::VIEW_MODE_MAGENTO:
                $gridClass = \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View\Magento\Grid::class;
                break;
            case Switcher::VIEW_MODE_SETTINGS:
                $gridClass = \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View\Settings\Grid::class;
                break;
            default:
                throw new \M2E\Kaufland\Model\Exception\Logic(sprintf('Unknown view mode - %s', $this->viewMode));
        }

        $this->addChild(
            'grid',
            $gridClass,
            ['listing' => $this->listing]
        );
    }

    protected function _toHtml()
    {

        return '<div id="listing_view_progress_bar"></div>' .
            '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
            '<div id="listing_view_content_container">' .
            parent::_toHtml() .
            '</div>';
    }

    public function getGridHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::getGridHtml();
        }

        $helper = $this->dataHelper;

        $this->jsUrl->addUrls(
            $helper->getControllerActions(
                'Kaufland\Listing',
                ['_current' => true]
            )
        );

        $path = 'kaufland_listing/transferring/index';
        $this->jsUrl->add(
            $this->getUrl(
                '*/' . $path,
                [
                    'listing_id' => $this->listing->getId(),
                ]
            ),
            $path
        );

        $this->jsUrl->add(
            $this->getUrl(
                '*/listing_mapping/mapProductPopupHtml',
                [
                    'account_id' => $this->listing->getAccountId(),
                    'storefront_id' => $this->listing->getStorefrontId(),
                ]
            ),
            'mapProductPopupHtml'
        );
        $this->jsUrl->add($this->getUrl('*/listing_mapping/remap'), 'listing_mapping/remap');

        $path = 'kaufland_listing_transferring/getListings';
        $this->jsUrl->add($this->getUrl('*/' . $path), $path);

        $this->jsTranslator->addTranslations(
            [
                'Remove Category' => __('Remove Category'),
                'Add New Rule' => __('Add New Rule'),
                'Add/Edit Categories Rule' => __('Add/Edit Categories Rule'),
                'Based on Magento Categories' => __('Based on Magento Categories'),
                'You must select at least 1 Category.' => __('You must select at least 1 Category.'),
                'Rule with the same Title already exists.' => __('Rule with the same Title already exists.'),
                'Compatibility Attribute' => __('Compatibility Attribute'),
                'Sell on Another Storefront' => __('Sell on Another Storefront'),
                'Create new' => __('Create new'),
                'Linking Product' => __('Linking Product'),
            ]
        );

        return parent::getGridHtml();
    }

    protected function getSettingsButtonDropDownItems()
    {
        $items = [];

        $backUrl = $this->urlHelper->makeBackUrlParam(
            '*/kaufland_listing/view',
            ['id' => $this->listing->getId()]
        );

        $url = $this->getUrl(
            '*/kaufland_listing/edit',
            [
                'id' => $this->listing->getId(),
                'back' => $backUrl,
            ]
        );
        $items[] = [
            'label' => __('Configuration'),
            'onclick' => 'window.open(\'' . $url . '\',\'_blank\');',
            'default' => true,
        ];

        return $items;
    }

    public function getAddProductsDropDownItems()
    {
        $items = [];

        $url = $this->getUrl(
            '*/kaufland_listing_product_add',
            [
                'source' => \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Product\Add\SourceMode::MODE_PRODUCT,
                'clear' => true,
                'id' => $this->listing->getId(),
            ]
        );
        $items[] = [
            'id' => 'add_products_mode_product',
            'label' => __('From Products List'),
            'onclick' => "setLocation('" . $url . "')",
            'default' => true,
        ];

        $url = $this->getUrl(
            '*/kaufland_listing_product_add',
            [
                'source' => \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Product\Add\SourceMode::MODE_CATEGORY,
                'clear' => true,
                'id' => $this->listing->getId(),
            ]
        );
        $items[] = [
            'id' => 'add_products_mode_category',
            'label' => __('From Categories'),
            'onclick' => "setLocation('" . $url . "')",
        ];

        return $items;
    }
}
