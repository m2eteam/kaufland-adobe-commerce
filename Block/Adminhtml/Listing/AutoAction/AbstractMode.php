<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\AutoAction;

abstract class AbstractMode extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock
{
    protected \Magento\Framework\Data\FormFactory $formFactory;
    private \M2E\Core\Helper\Magento\Store $magentoStoreHelper;
    private \M2E\Kaufland\Model\Listing $listing;

    public function __construct(
        \M2E\Kaufland\Model\Listing $listing,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Core\Helper\Magento\Store $magentoStoreHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->magentoStoreHelper = $magentoStoreHelper;
        $this->listing = $listing;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('listingAutoActionMode');
    }

    public function isAdminStore(): bool
    {
        return $this->listing->getStoreId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    public function getWebsiteName(): string
    {
        return $this->magentoStoreHelper->getWebsiteName($this->listing->getStoreId());
    }

    public function getHelpPageUrl(): string
    {
        return 'https://docs-m2.m2epro.com/docs/kaufland-auto-add-remove-rules/';
    }

    protected function _toHtml()
    {
        $titleHtml = sprintf(
            '<h3 id="block-title-top">%s</h3>',
            $this->getBlockTitle()
        );
        $contentHtml = sprintf(
            '<div id="block-content-wrapper" style="margin-left: 26px">%s</div>',
            $this->getBlockContent()
        );

        return $this->getHelpBlock()->toHtml()
            . $titleHtml
            . $contentHtml
            . parent::_toHtml();
    }

    protected function getBlockTitle(): string
    {
        return (string)__('Choose the level at which Products should be automatically added or deleted');
    }

    protected function getHelpBlock()
    {
        $helpBlockContent = __(
            '<p>Select the level at which Products should be automatically added to or removed ' .
            'from the Listing:</p><br>' .
            '<p><strong>Global</strong> — Monitors all Products added to or removed from the Magento Catalog</p>' .
            '<p><strong>Website</strong> — Monitors Products added to or removed ' .
            'from a specific Magento Website</p>' .
            '<p><strong>Category</strong> — Monitors Products added to or removed ' .
            'from a selected Magento Category</p><br>' .
            '<p>You can adjust these settings at any time by going to ' .
            '<strong>Edit Settings > Auto Add/Remove Rules</strong> in the Listing.</p><br>' .
            '<p>For more detailed instructions, please refer to the ' .
            '<a href="%url" target="_blank">documentation</a>.</p>',
            ['url' => $this->getHelpPageUrl()]
        );

        return $this
            ->getLayout()
            ->createBlock(\M2E\Kaufland\Block\Adminhtml\HelpBlock::class)
            ->setData([
                'id' => 'block_notice_listing_auto_action_mode',
                'content' => $helpBlockContent,
            ]);
    }

    protected function getBlockContent(): string
    {
        $form = $this->formFactory->create();

        $form->addField(
            'global',
            'radio',
            [
                'name' => 'auto_mode',
                'value' => \M2E\Kaufland\Model\Listing::AUTO_MODE_GLOBAL,
                'class' => 'admin__control-radio',
                'after_element_html' => __('Global (all Products)'),
            ]
        );

        $form->addField(
            'note_global',
            'note',
            [
                'text' => __('Acts when a Product is added or deleted from Magento Catalog.'),
            ]
        );

        if (!$this->isAdminStore()) {
            $form->addField(
                'website',
                'radio',
                [
                    'name' => 'auto_mode',
                    'value' => \M2E\Kaufland\Model\Listing::AUTO_MODE_WEBSITE,
                    'class' => 'admin__control-radio',
                    'after_element_html' => __('Website') . '&nbsp;(' . $this->getWebsiteName() . ')',
                ]
            );

            $form->addField(
                'note_website',
                'note',
                [
                    'text' => __(
                        'Acts when a Product is added to or deleted from the Website with regard ' .
                        'to the Store View specified for the %extension_title Listing.',
                        [
                            'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
                        ]
                    ),
                ]
            );
        }

        $form->addField(
            'category',
            'radio',
            [
                'name' => 'auto_mode',
                'value' => \M2E\Kaufland\Model\Listing::AUTO_MODE_CATEGORY,
                'class' => 'admin__control-radio validate-one-required-by-name',
                'after_element_html' => __('Category'),
            ]
        );

        $form->addField(
            'note_category',
            'note',
            [
                'text' => __('Acts when the Product is added to or deleted from the selected Magento Category.'),
            ]
        );

        $form->addField(
            'validation',
            'text',
            [
                'class' => 'M2ePro-validate-mode',
                'style' => 'display: none;',
            ]
        );

        $this->css->add('label.mage-error[for="validation"] { width: 220px !important; }');

        return $form->toHtml();
    }
}
