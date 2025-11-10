<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\AutoAction\Mode;

abstract class AbstractWebsite extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm
{
    public array $formData = [];
    private \M2E\Kaufland\Model\Listing $listing;

    private \M2E\Core\Helper\Magento\Store $magentoStoreHelper;

    public function __construct(
        \M2E\Kaufland\Model\Listing $listing,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Core\Helper\Magento\Store $magentoStoreHelper,
        array $data = []
    ) {
        $this->magentoStoreHelper = $magentoStoreHelper;
        $this->listing = $listing;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setId('listingAutoActionModeWebsite');
        $this->formData = $this->getFormData();
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $form->addField(
            'auto_mode',
            'hidden',
            [
                'name' => 'auto_mode',
                'value' => \M2E\Kaufland\Model\Listing::AUTO_MODE_WEBSITE,
            ]
        );

        $fieldSet = $form->addFieldset('auto_website_fieldset_container', []);

        $fieldSet->addField(
            'auto_website_adding_mode',
            self::SELECT,
            [
                'name' => 'auto_website_adding_mode',
                'label' => __('Product Added to Website'),
                'title' => __('Product Added to Website'),
                'values' => [
                    [
                        'value' => \M2E\Kaufland\Model\Listing::ADDING_MODE_NONE,
                        'label' => __('No Action')],
                ],
                'value' => $this->formData['auto_website_adding_mode'],
                'tooltip' => __('Action which will be applied automatically.'),
                'style' => 'width: 350px',
            ]
        );

        $fieldSet->addField(
            'auto_website_adding_add_not_visible',
            self::SELECT,
            [
                'name' => 'auto_website_adding_add_not_visible',
                'label' => __('Add not Visible Individually Products'),
                'title' => __('Add not Visible Individually Products'),
                'values' => [
                    [
                        'value' => \M2E\Kaufland\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_NO,
                        'label' => __('No'),
                    ],
                    [
                        'value' => \M2E\Kaufland\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
                        'label' => __('Yes'),
                    ],
                ],
                'value' => $this->formData['auto_website_adding_add_not_visible'],
                'field_extra_attributes' => 'id="auto_website_adding_add_not_visible_field"',
                'tooltip' => __(
                    'Set to <strong>Yes</strong> if you want the Magento Products with
                    Visibility \'Not visible Individually\' to be added to the Listing
                    Automatically.<br/>
                    If set to <strong>No</strong>, only Variation (i.e.
                    Parent) Magento Products will be added to the Listing Automatically,
                    excluding Child Products.'
                ),
            ]
        );

        $fieldSet->addField(
            'auto_website_deleting_mode',
            self::SELECT,
            [
                'name' => 'auto_website_deleting_mode',
                'label' => __('Product Deleted from Website'),
                'title' => __('Product Deleted from Website'),
                'values' => [
                    [
                        'value' => \M2E\Kaufland\Model\Listing::DELETING_MODE_NONE,
                        'label' => __('No Action'),
                    ],
                    [
                        'value' => \M2E\Kaufland\Model\Listing::DELETING_MODE_STOP,
                        'label' => __('Stop on Channel'),
                    ],
                    [
                        'value' => \M2E\Kaufland\Model\Listing::DELETING_MODE_STOP_REMOVE,
                        'label' => __('Stop on Channel and Delete from Listing'),
                    ],
                ],
                'value' => $this->formData['auto_website_deleting_mode'],
                'style' => 'width: 350px',
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function hasFormData(): bool
    {
        return $this->listing->getData('auto_mode') == \M2E\Kaufland\Model\Listing::AUTO_MODE_WEBSITE;
    }

    public function getFormData(): array
    {
        $formData = $this->listing->getData();
        $default = $this->getDefault();

        return array_merge($default, $formData);
    }

    public function getDefault(): array
    {
        return [
            'auto_website_adding_mode' => \M2E\Kaufland\Model\Listing::ADDING_MODE_NONE,
            'auto_website_adding_add_not_visible' => \M2E\Kaufland\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_website_deleting_mode' => \M2E\Kaufland\Model\Listing::DELETING_MODE_STOP_REMOVE,
        ];
    }

    protected function _afterToHtml($html)
    {
        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Listing::class)
        );

        $hasFormData = $this->hasFormData() ? 'true' : 'false';

        $this->js->add(
            <<<JS
        $('auto_website_adding_mode')
            .observe('change', ListingAutoActionObj.addingModeChange)
            .simulate('change');

        if ({$hasFormData}) {
            $('website_reset_button').show();
        }
JS
        );

        return parent::_afterToHtml($html);
    }

    protected function _toHtml()
    {
        $title = sprintf(
            '<div id="additional_autoaction_title_text" style="display: none">%s</div>',
            $this->getBlockTitle()
        );

        $content = sprintf(
            '<div id="block-content-wrapper"><div id="data_container">%s</div></div>',
            parent::_toHtml()
        );

        return $title . $content;
    }

    // ----------------------------------------

    private function getBlockTitle(): string
    {
        return __('Website') . ": {$this->getWebsiteName()}";
    }

    private function getWebsiteName(): string
    {
        return $this->magentoStoreHelper->getWebsiteName($this->listing->getStoreId());
    }
}
