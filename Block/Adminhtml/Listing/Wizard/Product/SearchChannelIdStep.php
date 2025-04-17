<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Product;

class SearchChannelIdStep extends \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    use \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;
    private \M2E\Kaufland\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Kaufland\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;

        parent::__construct($context, $data);
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('SearchKauflandProductIdForListingProducts');

        $this->prepareButtons(
            [
                'id' => 'add_products_search_continue',
                'class' => 'action-primary forward',
                'label' => __('Continue'),
                'onclick' => '',
                'data_attribute' => [
                    'mage-init' => [
                        'Kaufland/Listing/Wizard/Product/Search/ContinueProcessor' => [
                            'urlCheck' => $this->getUrl(
                                '*/listing_wizard_search/checkSearchResults',
                                ['id' => $this->getWizardIdFromRequest()]
                            ),
                            'urlContinue' => $this->getUrl(
                                '*/listing_wizard_search/completeStep',
                                ['id' => $this->getWizardIdFromRequest()],
                            ),
                            'urlEnableProductNewMode' => $this->getUrl(
                                '*/listing_wizard/enableCreateProductMode',
                                ['id' => $this->getWizardIdFromRequest()],
                            ),
                        ],
                    ],
                ],
            ],
            $this->uiWizardRuntimeStorage->getManager(),
        );
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('kaufland/listing/view.css');

        $text = __(
            'Since most of the Products already exist in %channel_title Catalog, %extension_title makes it possible
     to find them and to make a link between your Magento Products and existing %channel_title Products.',
            [
                'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
            ]
        );

        $text2 = __(
            'You can use a Manual Search for each added Product by clicking on the icon in the EAN Column of the Grid.',
        );
        $text22 = __('Also you can use Automatic Search for added Product(s) by choosing');
        $text3 = __('Search EAN Automatically');
        $text4 = __(
            'Option in a mass Actions bulk. The Search will be performed based on the Product Identifiers Settings',
        );
        $text5 = __('You can always set or change Settings of the source for EAN');
        $note = __('Note:');
        $text6 = __(
            'The process of Automatic Search might be time-consuming, depending on
                  the number of added Products the Search is applied to.',
        );

        $this->appendHelpBlock([
            'content' =>
                '<p>' . $text . '</p><br><p>' . $text2 . ' ' . $text22 . '<strong>' . ' ' . $text3 . ' ' . '</strong>' . $text4 . '</p><br>' .
                '<p>' . $text5 . '</p>' . '<br>' .
                '<p><strong>' . $note . ' ' . '</strong>' . $text6 . '</p>',
        ]);

        /** @var \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Product\SearchChannelIdStep\Grid $grid */
        $grid = $this->getLayout()->createBlock(
            SearchChannelIdStep\Grid::class,
            '',
            [
                'listing' => $this->uiListingRuntimeStorage->getListing(),
                'wizardManager' => $this->uiWizardRuntimeStorage->getManager(),
            ],
        );
        $this->setChild('grid', $grid);

        return parent::_prepareLayout();
    }
}
