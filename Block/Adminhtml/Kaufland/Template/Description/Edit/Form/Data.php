<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Description\Edit\Form;

use M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm;
use M2E\Kaufland\Model\Template\Description as DescriptionAlias;

class Data extends AbstractForm
{
    /** @var \M2E\Core\Helper\Magento\Attribute */
    protected $magentoAttributeHelper;

    /** @var \M2E\Kaufland\Helper\Data */
    private $dataHelper;

    /** @var \M2E\Kaufland\Helper\Data\GlobalData */
    private $globalDataHelper;

    private $attributes = [];
    /** @var \M2E\Kaufland\Model\Template\Description\BuilderFactory */
    private DescriptionAlias\BuilderFactory $templateDescriptionBuilderFactory;

    public function __construct(
        \M2E\Kaufland\Model\Template\Description\BuilderFactory $templateDescriptionBuilderFactory,
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Kaufland\Helper\Data $dataHelper,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        $this->dataHelper = $dataHelper;
        $this->globalDataHelper = $globalDataHelper;
        $this->templateDescriptionBuilderFactory = $templateDescriptionBuilderFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->attributes = $this->magentoAttributeHelper->getAll();
    }

    protected function _prepareForm()
    {
        $imgAttributes = $this->magentoAttributeHelper->filterByInputTypes(
            $this->attributes,
            ['text', 'image', 'media_image', 'gallery', 'multiline', 'textarea', 'select', 'multiselect']
        );

        $formData = $this->getFormData();

        $default = $this->getDefault();
        $formData = array_replace_recursive($default, $formData);

        $isCustomDescription = ($formData['description_mode'] == DescriptionAlias::DESCRIPTION_MODE_CUSTOM);

        $form = $this->_formFactory->create();
        $this->setForm($form);

        $form->addField(
            'description_id',
            'hidden',
            [
                'name' => 'description[id]',
                'value' => (!$this->isCustom() && isset($formData['id'])) ? (int)$formData['id'] : '',
            ]
        );

        $form->addField(
            'description_title',
            'hidden',
            [
                'name' => 'description[title]',
                'value' => $this->getTitle(),
            ]
        );

        $form->addField(
            'description_is_custom_template',
            'hidden',
            [
                'name' => 'description[is_custom_template]',
                'value' => $this->isCustom() ? 1 : 0,
            ]
        );

        $form->addField(
            'description_editor_type',
            'hidden',
            [
                'name' => 'description[editor_type]',
                'value' => $formData['editor_type'],
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_description_form_data_image',
            [
                'legend' => __('Images'),
                'collapsable' => true,
            ]
        );

        $preparedAttributes = [];
        foreach ($imgAttributes as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];
            if (
                $formData['image_main_mode'] == DescriptionAlias::IMAGE_MAIN_MODE_ATTRIBUTE
                && $formData['image_main_attribute'] == $attribute['code']
            ) {
                $attrs['selected'] = 'selected';
            }
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => DescriptionAlias::IMAGE_MAIN_MODE_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $fieldset->addField(
            'image_main',
            self::SELECT,
            [
                'name' => 'description[image_main_mode]',
                'label' => __('Main Image'),
                'values' => [
                    DescriptionAlias::IMAGE_MAIN_MODE_PRODUCT => __('Product Base Image'),
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'is_magento_attribute' => true,
                        ],
                    ],
                ],
                'value' => $formData['image_main_mode'] != DescriptionAlias::IMAGE_MAIN_MODE_ATTRIBUTE
                    ? $formData['image_main_mode'] : '',
                'create_magento_attribute' => true,
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text,textarea,select,multiselect');

        $fieldset->addField(
            'image_main_attribute',
            'hidden',
            [
                'name' => 'description[image_main_attribute]',
                'value' => $formData['image_main_attribute'],
            ]
        );

        $fieldset->addField(
            'gallery_images_limit',
            'hidden',
            [
                'name' => 'description[gallery_images_limit]',
                'value' => $formData['gallery_images_limit'],
            ]
        );

        $fieldset->addField(
            'gallery_images_attribute',
            'hidden',
            [
                'name' => 'description[gallery_images_attribute]',
                'value' => $formData['gallery_images_attribute'],
            ]
        );

        $preparedImages = [];
        for ($i = 1; $i <= DescriptionAlias\Source::GALLERY_IMAGES_COUNT_MAX; $i++) {
            $attrs = ['attribute_code' => $i];

            if (
                $i == $formData['gallery_images_limit']
                && $formData['gallery_images_mode'] == DescriptionAlias::GALLERY_IMAGES_MODE_PRODUCT
            ) {
                $attrs['selected'] = 'selected';
            }

            $preparedImages[] = [
                'value' => DescriptionAlias::GALLERY_IMAGES_MODE_PRODUCT,
                'label' => $i == 1 ? $i : (__('Up to') . " $i"),
                'attrs' => $attrs,
            ];
        }

        $preparedAttributes = [];
        foreach ($imgAttributes as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];

            if (
                $formData['gallery_images_mode'] == DescriptionAlias::GALLERY_IMAGES_MODE_ATTRIBUTE
                && $formData['gallery_images_attribute'] == $attribute['code']
            ) {
                $attrs['selected'] = 'selected';
            }

            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => DescriptionAlias::GALLERY_IMAGES_MODE_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $fieldset->addField(
            'gallery_images',
            self::SELECT,
            [
                'container_id' => 'gallery_images_mode_tr',
                'name' => 'description[gallery_images_mode]',
                'label' => __('Gallery Images'),
                'values' => [
                    DescriptionAlias::GALLERY_IMAGES_MODE_NONE => __('None'),
                    [
                        'label' => __('Product Images'),
                        'value' => $preparedImages,
                    ],
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'is_magento_attribute' => true,
                        ],
                    ],
                ],
                'create_magento_attribute' => true,
                'tooltip' => __(
                    'Adds small thumbnails that appear under the large Base Image.
                     You can add up to 8 additional photos to each Listing on Kaufland.
                        <br/><b>Note:</b> Text, Multiple Select or Dropdown type Attribute can be used.
                        The value of Attribute must contain absolute urls.
                        <br/>In Text type Attribute urls must be separated with comma.
                        <br/>e.g. http://mymagentostore.com/images/baseimage1.jpg,
                        http://mymagentostore.com/images/baseimage2.jpg'
                ),
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text,textarea,select,multiselect');

        $fieldset = $form->addFieldset(
            'magento_block_template_description_form_data_description',
            [
                'legend' => __('Description'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'title_mode',
            'select',
            [
                'label' => __('Title'),
                'name' => 'description[title_mode]',
                'values' => [
                    DescriptionAlias::TITLE_MODE_PRODUCT => __('Product Name'),
                    DescriptionAlias::TITLE_MODE_CUSTOM => __('Custom Value'),
                ],
                'value' => $formData['title_mode'],
                'tooltip' => __(
                    'This is the Title that Buyers will see on Kaufland. A good Title ensures better visibility.'
                ),
            ]
        );

        $preparedAttributes = [];
        foreach ($this->attributes as $attribute) {
            $preparedAttributes[] = [
                'value' => $attribute['code'],
                'label' => $attribute['label'],
            ];
        }

        $button = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Magento\Button\MagentoAttribute::class
        )
                       ->addData(
                           [
                               'label' => __('Insert'),
                               'destination_id' => 'title_template',
                               'class' => 'primary',
                               'style' => 'display: inline-block;',
                           ]
                       );

        $selectAttrBlock = $this->elementFactory->create(
            self::SELECT,
            [
                'data' => [
                    'values' => $preparedAttributes,
                    'class' => 'Kaufland-required-when-visible magento-attribute-custom-input',
                    'create_magento_attribute' => true,
                ],
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text,select,multiselect,boolean,price,date')
                                                ->addCustomAttribute('apply_to_all_attribute_sets', 'false');

        $selectAttrBlock->setId('selectAttr_title_template');
        $selectAttrBlock->setForm($this->_form);

        $fieldset->addField(
            'title_template',
            'text',
            [
                'container_id' => 'custom_title_tr',
                'label' => __('Title Value'),
                'value' => $formData['title_template'],
                'name' => 'description[title_template]',
                'class' => 'input-text-title',
                'required' => true,
                'after_element_html' => $selectAttrBlock->toHtml() . $button->toHtml(),
            ]
        );

        $preparedAttributes = [];
        foreach ($this->attributes as $attribute) {
            $preparedAttributes[] = [
                'value' => $attribute['code'],
                'label' => $attribute['label'],
            ];
        }

        $button = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)->addData(
            [
                'label' => __('Preview'),
                'onclick' => 'KauflandTemplateDescriptionObj.openPreviewPopup()',
                'class' => 'action-primary',
                'style' => 'margin-left: 70px;',
            ]
        );

        $tooltipMessage = __(
            'Choose whether to use Magento <strong>Product Description</strong>
            or <strong>Product Short Description</strong> for the Kaufland Listing Description.'
        );
        $fieldset->addField(
            'description_mode',
            'select',
            [
                'label' => __('Description'),
                'name' => 'description[description_mode]',
                'values' => [
                    DescriptionAlias::DESCRIPTION_MODE_PRODUCT => __('Product Description'),
                    DescriptionAlias::DESCRIPTION_MODE_SHORT => __('Product Short Description'),
                    DescriptionAlias::DESCRIPTION_MODE_CUSTOM => __('Custom Value'),
                ],
                'value' => $this->isEdit() ? $formData['description_mode'] : '-1',
                'class' => 'Kaufland-validate-description-mode',
                'required' => true,
                'after_element_html' => $this->getTooltipHtml($tooltipMessage) . $button->toHtml(),
            ]
        );

        if ($isCustomDescription) {
            $fieldset->addField(
                'view_edit_custom_description_link',
                'link',
                [
                    'container_id' => 'view_edit_custom_description',
                    'label' => '',
                    'value' => __('View / Edit Custom Description'),
                    'onclick' => 'KauflandTemplateDescriptionObj.view_edit_custom_change()',
                    'href' => 'javascript://',
                    'style' => 'text-decoration: underline;',
                ]
            );
        }

        $showHideWYSIWYGButton = '';
        if ($this->wysiwygConfig->isEnabled()) {
            $showHideWYSIWYGButtonBlock = $this
                ->getLayout()
                ->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)
                ->setData(
                    [
                        'id' => 'description_template_show_hide_wysiwyg',
                        'label' => ($formData['editor_type'] == DescriptionAlias::EDITOR_TYPE_SIMPLE)
                            ? __('Show Editor') : __('Hide Editor'),
                        'class' => 'action-primary hidden',
                    ]
                );

            $showHideWYSIWYGButton = $showHideWYSIWYGButtonBlock->toHtml();
        }

        $openCustomInsertsButton = $this->getLayout()
                                        ->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)
                                        ->setData(
                                            [
                                                'id' => 'custom_inserts_open_popup',
                                                'label' => __('Insert Customs'),
                                                'class' => 'action-primary',
                                            ]
                                        );

        $fieldset->addField(
            'description_template',
            'editor',
            [
                'container_id' => 'description_template_tr',
                'css_class' => 'c-custom_description_tr _required',
                'label' => __('Description Value'),
                'name' => 'description[description_template]',
                'value' => $formData['description_template'],
                'class' => ' admin__control-textarea left Kaufland-validate-description-template',
                'wysiwyg' => $this->wysiwygConfig->isEnabled(),
                'force_load' => true,
                'config' => $this->wysiwygConfig->getConfig(
                    [
                        'hidden' => true,
                        'enabled' => false,
                        'no_display' => false,
                        'add_variables' => false,
                        'force_load' => true,
                    ]
                ),
                'after_element_html' => <<<HTML
<div id="description_template_buttons">
    {$showHideWYSIWYGButton}
    {$openCustomInsertsButton->toHtml()}
</div>
HTML
                ,
            ]
        );

        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Template\Description::class)
        );

        $this->jsUrl->addUrls(
            [
                'kaufland_template_description' => $this->getUrl(
                    '*/kaufland_template_description/saveWatermarkImage/'
                ),
            ]
        );

        $this->jsUrl->addUrls($this->dataHelper->getControllerActions('Kaufland_Template_Description'));

        $this->jsTranslator->addTranslations(
            [
                'Adding Image' => __('Adding Image'),
                'Custom Insertions' => __('Custom Insertions'),
                'Show Editor' => __('Show Editor'),
                'Hide Editor' => __('Hide Editor'),
                'Description Preview' => __('Description Preview'),
                'Please enter a valid Magento product ID.' => __('Please enter a valid Magento product ID.'),
                'Please enter Description Value.' => __('Please enter Description Value.'),
            ]
        );

        $this->js->add(
            <<<JS
    require([
        'Kaufland/Kaufland/Template/Description',
        'Kaufland/Plugin/Magento/Attribute/Button'
    ], function(){
        window.KauflandTemplateDescriptionObj = new KauflandTemplateDescription();
        setTimeout(function() {
            KauflandTemplateDescriptionObj.initObservers();
        }, 50);

        window.MagentoAttributeButtonObj = new MagentoAttributeButton();
    });
JS
        );

        return parent::_prepareForm();
    }

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->getCustomInsertsHtml()
            . $this->getDescriptionPreviewHtml();
    }

    public function isCustom()
    {
        if (isset($this->_data['is_custom'])) {
            return (bool)$this->_data['is_custom'];
        }

        return false;
    }

    public function isEdit()
    {
        $template = $this->globalDataHelper->getValue('kaufland_template_description');

        if ($template === null || $template->getId() === null) {
            return false;
        }

        return true;
    }

    public function getTitle()
    {
        if ($this->isCustom()) {
            return isset($this->_data['custom_title']) ? $this->_data['custom_title'] : '';
        }

        $template = $this->globalDataHelper->getValue('kaufland_template_description');

        if (!$this->isEdit()) {
            return '';
        }

        return $template->getTitle();
    }

    public function getFormData()
    {
        if (!$this->isEdit()) {
            return [];
        }

        $template = $this->globalDataHelper->getValue('kaufland_template_description');

        $data = $template->getData();

        unset($data['variation_configurable_images']);

        return $data;
    }

    public function getDefault()
    {
        $default = $this->templateDescriptionBuilderFactory->create()->getDefaultData();

        $default['variation_configurable_images'] = \M2E\Core\Helper\Json::decode(
            $default['variation_configurable_images']
        );

        return $default;
    }

    protected function getCustomInsertsHtml()
    {
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('custom_inserts', ['legend' => __('Attribute')]);

        $preparedAttributes = [];
        foreach ($this->attributes as $attribute) {
            $preparedAttributes[] = [
                'value' => $attribute['code'],
                'label' => $attribute['label'],
            ];
        }

        $button = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)->setData(
            [
                'label' => __('Insert'),
                'class' => 'action-primary',
                'onclick' => 'KauflandTemplateDescriptionObj.insertProductAttribute()',
                'style' => 'margin-left: 15px;',
            ]
        );

        $fieldset->addField(
            'custom_inserts_product_attribute',
            self::SELECT,
            [
                'label' => __('Magento Product'),
                'class' => 'Kaufland-custom-attribute-can-be-created',
                'values' => $preparedAttributes,
                'after_element_html' => $button->toHtml(),
                'apply_to_all_attribute_sets' => 0,
            ]
        )->addCustomAttribute('apply_to_all_attribute_sets', 0);

        $KauflandAttributes = [
            'title' => __('Title'),
            'fixed_price' => __('Kaufland Price'),
            'qty' => __('QTY'),
        ];

        $button->setData('onclick', 'KauflandTemplateDescriptionObj.insertKauflandAttribute()');

        $fieldset->addField(
            'custom_inserts_kaufland_attribute',
            'select',
            [
                'label' => __('M2E Kaufland'),
                'values' => $KauflandAttributes,
                'after_element_html' => $button->toHtml(),
            ]
        );

        return <<<HTML
<div class="hidden">
    <div id="custom_inserts_popup" class="admin__old">{$form->toHtml()}</div>
</div>
HTML;
    }

    private function getDescriptionPreviewHtml()
    {
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('description_preview_fieldset', ['legend' => '']);

        $fieldset->addField(
            'description_preview_help_block',
            self::HELP_BLOCK,
            [
                'content' => __(
                    '
                    If you would like to preview the Description data for the particular Magento Product, please,
                    provide its ID into the <strong>Magento Product ID</strong> input and select
                    a <strong>Magento Store View</strong> the values
                    should be taken from. As a result you will see the Item Description which will be sent to
                    Kaufland basing on the settings you specified.<br />

                    Also, you can press a <strong>Select Randomly</strong> button to allow M2E Kaufland
                    to automatically select the most suitable Product for its previewing.'
                ),
            ]
        );

        $button = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)->addData(
            [
                'label' => __('Select Randomly'),
                'onclick' => 'KauflandTemplateDescriptionObj.selectProductIdRandomly()',
                'class' => 'action-primary',
                'style' => 'margin-left: 25px',
            ]
        );

        $fieldset->addField(
            'description_preview_magento_product_id',
            'text',
            [
                'label' => __('Magento Product ID'),
                'after_element_html' => $button->toHtml(),
                'class' => 'Kaufland-required-when-visible validate-digits
                                         Kaufland-validate-magento-product-id',
                'css_class' => '_required',
                'style' => 'width: 200px',
                'name' => 'description_preview[magento_product_id]',
            ]
        );

        $fieldset->addField(
            'description_preview_store_id',
            self::STORE_SWITCHER,
            [
                'label' => __('Store View'),
                'name' => 'description_preview[store_id]',
            ]
        );

        $fieldset->addField(
            'description_preview_description_mode',
            'hidden',
            [
                'name' => 'description_preview[description_mode]',
            ]
        );
        $fieldset->addField(
            'description_preview_description_template',
            'hidden',
            [
                'name' => 'description_preview[description_template]',
            ]
        );

        $fieldset->addField(
            'description_preview_form_key',
            'hidden',
            [
                'name' => 'form_key',
                'value' => $this->formKey->getFormKey(),
            ]
        );

        return <<<HTML
<div class="hidden">
    <div id="description_preview_popup" class="admin__old">{$form->toHtml()}</div>
</div>
HTML;
    }
}
