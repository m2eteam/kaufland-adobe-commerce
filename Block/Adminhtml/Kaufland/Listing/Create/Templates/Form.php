<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Create\Templates;

use M2E\Kaufland\Model\Listing;
use M2E\Kaufland\Model\Template\Manager as TemplateManager;

class Form extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm
{
    protected ?Listing $listing = null;
    private \M2E\Kaufland\Helper\Data\Session $sessionDataHelper;
    private Listing\Repository $listingRepository;
    private \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat\CollectionFactory $sellingFormatCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Template\Synchronization\CollectionFactory $synchronizationCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Template\Shipping\CollectionFactory $shippingCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Template\Description\CollectionFactory $descriptionCollectionFactory;
    private \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper;

    public function __construct(
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat\CollectionFactory $sellingFormatCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Template\Synchronization\CollectionFactory $synchronizationCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Template\Shipping\CollectionFactory $shippingCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Template\Description\CollectionFactory $descriptionCollectionFactory,
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Kaufland\Helper\Data\Session $sessionDataHelper,
        array $data = []
    ) {
        $this->sessionDataHelper = $sessionDataHelper;
        $this->listingRepository = $listingRepository;
        $this->sellingFormatCollectionFactory = $sellingFormatCollectionFactory;
        $this->synchronizationCollectionFactory = $synchronizationCollectionFactory;
        $this->shippingCollectionFactory = $shippingCollectionFactory;
        $this->descriptionCollectionFactory = $descriptionCollectionFactory;
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'method' => 'post',
                    'action' => $this->getUrl('*/kaufland_listing/save'),
                ],
            ]
        );

        $formData = $this->getListingData();

        $form->addField(
            'storefront_id',
            'hidden',
            [
                'value' => $formData['storefront_id'],
            ]
        );

        $form->addField(
            'store_id',
            'hidden',
            [
                'value' => $formData['store_id'],
            ]
        );

        $attributes = $this->magentoAttributeHelper->getAll();
        $attributesByTypes = [
            'text' => $this->magentoAttributeHelper->filterByInputTypes(
                $attributes,
                ['text']
            ),
        ];

        $fieldset = $form->addFieldset(
            'selling_settings',
            [
                'legend' => __('Selling'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'template_selling_format_messages',
            self::CUSTOM_CONTAINER,
            [
                'style' => 'display: block;',
                'css_class' => 'Kaufland-fieldset-table no-margin-bottom',
            ]
        );

        $sellingFormatTemplates = $this->getSellingFormatTemplates();
        $style = count($sellingFormatTemplates) === 0 ? 'display: none' : '';

        $templateSellingFormatValue = $formData['template_selling_format_id'];
        if (empty($templateSellingFormatValue) && !empty($sellingFormatTemplates)) {
            $templateSellingFormatValue = reset($sellingFormatTemplates)['value'];
        }

        $templateSellingFormat = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_selling_format_id',
                    'name' => 'template_selling_format_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => array_merge(['' => ''], $sellingFormatTemplates),
                    'value' => $templateSellingFormatValue,
                    'required' => true,
                ],
            ]
        );
        $templateSellingFormat->setForm($form);

        $style = count($sellingFormatTemplates) === 0 ? '' : 'display: none';
        $noPoliciesAvailableText = __('No Policies available.');
        $viewText = __('View');
        $editText = __('Edit');
        $orText = __('or');
        $addNewText = __('Add New');
        $fieldset->addField(
            'template_selling_format_container',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Selling Policy'),
                'style' => 'line-height: 34px;display: initial;',
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required' => true,
                'text' => <<<HTML
    <span id="template_selling_format_label" style="{$style}">
        $noPoliciesAvailableText
    </span>
    {$templateSellingFormat->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_selling_format_template_link" style="color:#41362f">
        <a href="javascript: void(0);" style="" onclick="KauflandListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_SELLING_FORMAT)}',
            $('template_selling_format_id').value,
            KauflandListingSettingsObj.newSellingFormatTemplateCallback
        );">
            $viewText&nbsp;/&nbsp;$editText
        </a>
        <span>$orText</span>
    </span>
   <a id="add_selling_format_template_link" href="javascript: void(0);"
        onclick="KauflandListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($formData['storefront_id'], TemplateManager::TEMPLATE_SELLING_FORMAT)}',
        KauflandListingSettingsObj.newSellingFormatTemplateCallback
    );">$addNewText</a>
</span>
HTML
                ,
            ]
        );

        $descriptionTemplates = $this->getDescriptionTemplates();
        $style = count($descriptionTemplates) === 0 ? 'display: none' : '';

        $templateDescriptionValue = $formData['template_description_id'];
        if (empty($templateDescriptionValue) && !empty($descriptionTemplates)) {
            $templateDescriptionValue = reset($descriptionTemplates)['value'];
        }

        $templateDescription = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_description_id',
                    'name' => 'template_description_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => array_merge(['' => ''], $descriptionTemplates),
                    'value' => $templateDescriptionValue,
                ],
            ]
        );
        $templateDescription->setForm($form);

        $style = count($descriptionTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_description_container',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Description Policy'),
                'style' => 'line-height: 34px;display: initial;',
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required' => (bool)$this->getRequest()->getParam('isDescriptionRequired'),
                'text' => <<<HTML
    <span id="template_description_label" style="{$style}">
        $noPoliciesAvailableText
    </span>
    {$templateDescription->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_description_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="KauflandListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_DESCRIPTION)}',
            $('template_description_id').value,
            KauflandListingSettingsObj.newDescriptionTemplateCallback
        );">
            $viewText&nbsp;/&nbsp;$editText
        </a>
        <span>$orText</span>
    </span>
    <a id="add_description_template_link" href="javascript: void(0);"
        onclick="KauflandListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($formData['storefront_id'], TemplateManager::TEMPLATE_DESCRIPTION)}',
        KauflandListingSettingsObj.newDescriptionTemplateCallback
    );">$addNewText</a>
</span>
HTML
                ,
            ]
        );

        $fieldset->addField(
            'condition_value',
            self::SELECT,
            [
                'name' => 'condition_value',
                'label' => $this->__('Condition'),
                'value' => $formData['condition_value'],
                'values' => $this->getRecommendedConditionValues(),
                'tooltip' => $this->__(
                    '<p>Specify the condition that best describes the current state of your product.</p><br>

                    <p>By providing accurate information about the product condition, you improve the visibility
                    of your listings, ensure fair pricing, and increase customer satisfaction.</p>'
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'sku_settings_fieldset',
            [
                'legend' => __('SKU Settings'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'sku_custom_attribute',
            'hidden',
            [
                'name' => 'sku_settings[sku_custom_attribute]',
                'value' => $formData['sku_settings']['sku_custom_attribute'],
            ]
        );

        $preparedAttributes = [];
        foreach ($attributesByTypes['text'] as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];

            if (
                $formData['sku_settings']['sku_mode'] == Listing\Settings\Sku::SKU_MODE_CUSTOM_ATTRIBUTE
                && $attribute['code'] == $formData['sku_settings']['sku_custom_attribute']
            ) {
                $attrs['selected'] = 'selected';
            }
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => Listing\Settings\Sku::SKU_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $this->getRecommendedConditionValues();

        $fieldset->addField(
            'sku_mode',
            'hidden',
            [
                'name' => 'sku_settings[sku_mode]',
                'value' => $formData['sku_settings']['sku_mode'],
            ]
        );

        $fieldset->addField(
            'sku_mode_select',
            self::SELECT,
            [
                'label' => __('Source'),
                'values' => [
                    Listing\Settings\Sku::SKU_MODE_PRODUCT_ID => __('Product ID'),
                    Listing\Settings\Sku::SKU_MODE_DEFAULT => __('Product SKU'),
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'is_magento_attribute' => true,
                        ],
                    ],
                ],
                'value' => $formData['sku_settings']['sku_mode'] != Listing\Settings\Sku::SKU_MODE_CUSTOM_ATTRIBUTE ?
                    $formData['sku_settings']['sku_mode'] : '',
                'tooltip' => __(
                    'Select the Magento attribute that contains a unique SKU value.
    This SKU will be used to identify the Product when listing on the %channel_title channel.',
                    ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]
                ),

                'create_magento_attribute' => true,
            ]
        );

        $fieldset->addField(
            'sku_modification_mode',
            'hidden',
            [
                'name' => 'sku_settings[sku_modification_mode]',
                'value' => $formData['sku_settings']['sku_modification_mode'],
            ]
        );

        $fieldset->addField(
            'sku_modification_mode_select',
            self::SELECT,
            [
                'label' => __('Modification'),
                'values' => [
                    Listing\Settings\Sku::SKU_MODIFICATION_MODE_NONE => $this->__('None'),
                    Listing\Settings\Sku::SKU_MODIFICATION_MODE_PREFIX => $this->__('Prefix'),
                    Listing\Settings\Sku::SKU_MODIFICATION_MODE_POSTFIX => $this->__('Postfix'),
                ],
                'value' => $formData['sku_settings']['sku_modification_mode'],
                'tooltip' => __(
                    'Choose from the available options to modify %channel_title Item SKU from the Source attribute',
                    ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]
                ),
            ]
        );

        $fieldStyle = '';
        if ($formData['sku_settings']['sku_modification_mode'] == Listing\Settings\Sku::SKU_MODIFICATION_MODE_NONE) {
            $fieldStyle = 'style="display: none"';
        }

        $fieldset->addField(
            'sku_modification_custom_value',
            'text',
            [
                'container_id' => 'sku_modification_custom_value_tr',
                'label' => __('Modification Value'),
                'name' => 'sku_settings[sku_modification_custom_value]',
                'required' => true,
                'value' => $formData['sku_settings']['sku_modification_custom_value'],
                'class' => 'Kaufland-validate-sku-modification-custom-value
                    Kaufland-validate-sku-modification-custom-value-max-length',
                'field_extra_attributes' => $fieldStyle,
            ]
        );

        $fieldset->addField(
            'generate_sku_mode',
            self::SELECT,
            [
                'label' => __('Generate'),
                'name' => 'sku_settings[generate_sku_mode]',
                'values' => [
                    Listing\Settings\Sku::GENERATE_SKU_MODE_NO => __('No'),
                    Listing\Settings\Sku::GENERATE_SKU_MODE_YES => __('Yes'),
                ],
                'value' => $formData['sku_settings']['generate_sku_mode'],
                'tooltip' => __(
                    'Enable this setting to automatically generate a random, unique SKU for Products that will be listed on the %channel_title channel.
    This option may be helpful when listing the same Magento product on the %channel_title more than once.',
                    ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'shipping',
            [
                'legend' => __('Shipping'),
                'collapsable' => false,
            ]
        );

        $shippingTemplates = $this->getShippingTemplates();
        $style = count($shippingTemplates) === 0 ? 'display: none' : '';

        $templateShippingValue = $formData['template_shipping_id'];
        if (empty($templateShippingValue) && !empty($shippingTemplates)) {
            $templateShippingValue = reset($shippingTemplates)['value'];
        }

        $templateShipping = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_shipping_id',
                    'name' => 'template_shipping_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => array_merge(['' => ''], $shippingTemplates),
                    'value' => $templateShippingValue,
                    'required' => true,
                ],
            ]
        );
        $templateShipping->setForm($form);

        $accountId = (int)$formData['account_id'];
        $storefrontId = (int)$formData['storefront_id'];
        $style = count($shippingTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_shipping_container',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Shipping Policy'),
                'style' => 'line-height: 34px;display: initial;',
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required' => true,
                'text' => <<<HTML
    <span id="template_shipping_label" style="{$style}">
        $noPoliciesAvailableText
    </span>
    {$templateShipping->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_shipping_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="KauflandListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_SHIPPING)}',
            $('template_shipping_id').value,
            KauflandListingSettingsObj.newShippingTemplateCallback
        );">
            $viewText&nbsp;/&nbsp;$editText
        </a>
        <span>$orText</span>
    </span>
    <a id="add_shipping_template_link" href="javascript: void(0);"
        onclick="KauflandListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($storefrontId, TemplateManager::TEMPLATE_SHIPPING, $accountId)}',
        KauflandListingSettingsObj.newShippingTemplateCallback
    );">$addNewText</a>
</span>
HTML
                ,
            ]
        );

        $fieldset = $form->addFieldset(
            'synchronization_settings',
            [
                'legend' => __('Synchronization'),
                'collapsable' => false,
            ]
        );

        $synchronizationTemplates = $this->getSynchronizationTemplates();
        $style = count($synchronizationTemplates) === 0 ? 'display: none' : '';

        $templateSynchronizationValue = $formData['template_synchronization_id'];
        if (empty($templateSynchronizationValue) && !empty($synchronizationTemplates)) {
            $templateSynchronizationValue = reset($synchronizationTemplates)['value'];
        }

        $templateSynchronization = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_synchronization_id',
                    'name' => 'template_synchronization_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => array_merge(['' => ''], $synchronizationTemplates),
                    'value' => $templateSynchronizationValue,
                    'required' => true,
                ],
            ]
        );
        $templateSynchronization->setForm($form);

        $style = count($synchronizationTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_synchronization_container',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Synchronization Policy'),
                'style' => 'line-height: 34px;display: initial;',
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required' => true,
                'text' => <<<HTML
    <span id="template_synchronization_label" style="{$style}">
        $noPoliciesAvailableText
    </span>
    {$templateSynchronization->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_synchronization_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="KauflandListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_SYNCHRONIZATION)}',
            $('template_synchronization_id').value,
            KauflandListingSettingsObj.newSynchronizationTemplateCallback
        );">
            $viewText&nbsp;/&nbsp;$editText
        </a>
        <span>$orText</span>
    </span>
    <a id="add_synchronization_template_link" href="javascript: void(0);"
        onclick="KauflandListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($formData['storefront_id'], TemplateManager::TEMPLATE_SYNCHRONIZATION)}',
        KauflandListingSettingsObj.newSynchronizationTemplateCallback
    );">$addNewText</a>
</span>
HTML
                ,
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        $formData = $this->getListingData();

        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Helper\Component\Kaufland::class)
        );

        $this->jsUrl->addUrls(
            [
                'templateCheckMessages' => $this->getUrl(
                    '*/template/checkMessages',
                    [
                        'component_mode' => \M2E\Kaufland\Helper\Component\Kaufland::NICK,
                    ]
                ),
                'getSellingFormatTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'Template_SellingFormat',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir' => 'ASC',
                        'component_mode' => \M2E\Kaufland\Helper\Component\Kaufland::NICK,
                        'is_custom_template' => 0,
                    ]
                ),
                'getSynchronizationTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'Template_Synchronization',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir' => 'ASC',
                        'component_mode' => \M2E\Kaufland\Helper\Component\Kaufland::NICK,
                        'is_custom_template' => 0,
                    ]
                ),
                'getShippingTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'Template_Shipping',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir' => 'ASC',
                        'component_mode' => \M2E\Kaufland\Helper\Component\Kaufland::NICK,
                        'is_custom_template' => 0,
                        'storefront_id' => $formData['storefront_id'],
                    ]
                ),
                'getDescriptionTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'Template_Description',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir' => 'ASC',
                        'component_mode' => \M2E\Kaufland\Helper\Component\Kaufland::NICK,
                        'is_custom_template' => 0,
                    ]
                ),
            ]
        );

        $this->js->addOnReadyJs(
            <<<JS
    require([
        'Kaufland/TemplateManager',
        'Kaufland/Kaufland/Listing/Settings'
    ], function(){
        TemplateManagerObj = new TemplateManager();
        KauflandListingSettingsObj = new KauflandListingSettings();
        KauflandListingSettingsObj.initObservers();
    });
JS
        );

        return parent::_prepareLayout();
    }

    public function getDefaultFieldsValues()
    {
        return [
            'sku_settings' => [
                'sku_mode' => Listing\Settings\Sku::SKU_MODE_DEFAULT,
                'sku_custom_attribute' => '',
                'sku_modification_mode' => Listing\Settings\Sku::SKU_MODIFICATION_MODE_NONE,
                'sku_modification_custom_value' => '',
                'generate_sku_mode' => Listing\Settings\Sku::GENERATE_SKU_MODE_NO,
            ],
            'template_selling_format_id' => '',
            'template_synchronization_id' => '',
            'template_shipping_id' => '',
            'template_description_id' => '',
            'condition_value' => \M2E\Kaufland\Model\Listing::CONDITION_NEW,
        ];
    }

    protected function getListingData(): ?array
    {
        if ($this->getRequest()->getParam('id') !== null) {
            $data = array_merge($this->getListing()->getData(), $this->getListing()->getData());

            $skuSettings = $this->getListing()->getSkuSettings();
            $settingsData['sku_settings'] = [
                'generate_sku_mode' => $skuSettings->getGenerateSkuMode(),
                'sku_mode' => $skuSettings->getSkuMode(),
                'sku_custom_attribute' => $skuSettings->getSkuCustomAttribute(),
                'sku_modification_custom_value' => $skuSettings->getSkuModificationCustomValue(),
                'sku_modification_mode' => $skuSettings->getSkuModificationMode(),
            ];
            $data = array_merge($data, $settingsData);
        } else {
            $data = $this->sessionDataHelper->getValue(Listing::CREATE_LISTING_SESSION_DATA);
            $data = array_merge($this->getDefaultFieldsValues(), $data);
        }

        return $data;
    }

    protected function getListing(): ?Listing
    {
        $listingId = $this->getRequest()->getParam('id');
        if ($this->listing === null && $listingId) {
            $this->listing = $this->listingRepository->get((int)$listingId);
        }

        return $this->listing;
    }

    protected function getSellingFormatTemplates()
    {
        $collection = $this->sellingFormatCollectionFactory->create();
        $collection->addFieldToFilter('is_custom_template', 0);
        $collection->setOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(
            [
                'value' => \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_ID,
                'label' => \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat::COLUMN_TITLE,
            ]
        );

        $result = $collection->toArray();

        return $result['items'];
    }

    protected function getSynchronizationTemplates(): array
    {
        $collection = $this->synchronizationCollectionFactory->create();
        $collection->addFieldToFilter('is_custom_template', 0);
        $collection->setOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(
            [
                'value' => \M2E\Kaufland\Model\ResourceModel\Template\Synchronization::COLUMN_ID,
                'label' => \M2E\Kaufland\Model\ResourceModel\Template\Synchronization::COLUMN_TITLE,
            ]
        );

        return $collection->getConnection()->fetchAssoc($collection->getSelect());
    }

    protected function getShippingTemplates(): array
    {
        $formData = $this->getListingData();
        $collection = $this->shippingCollectionFactory->create();
        $collection->addFieldToFilter('is_custom_template', 0);
        $collection->addFieldToFilter('storefront_id', ($formData['storefront_id']));
        $collection->setOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(
            [
                'value' => \M2E\Kaufland\Model\ResourceModel\Template\Shipping::COLUMN_ID,
                'label' => \M2E\Kaufland\Model\ResourceModel\Template\Shipping::COLUMN_TITLE,
            ]
        );

        return $collection->getConnection()->fetchAssoc($collection->getSelect());
    }

    protected function getDescriptionTemplates(): array
    {
        $collection = $this->descriptionCollectionFactory->create();
        $collection->addFieldToFilter('is_custom_template', 0);
        $collection->setOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(
            [
                'value' => \M2E\Kaufland\Model\ResourceModel\Template\Description::COLUMN_ID,
                'label' => \M2E\Kaufland\Model\ResourceModel\Template\Description::COLUMN_TITLE,
            ]
        );

        return $collection->getConnection()->fetchAssoc($collection->getSelect());
    }

    protected function getAddNewUrl(int $storefrontId, string $nick, $accountId = null)
    {
        $params = [
            'storefront_id' => $storefrontId,
            'wizard' => $this->getRequest()->getParam('wizard'),
            'nick' => $nick,
            'close_on_save' => 1,
        ];

        if ($accountId !== null) {
            $params['account_id'] = $accountId;
        }

        return $this->getUrl('*/kaufland_template/newAction', $params);
    }

    protected function getEditUrl($nick)
    {
        return $this->getUrl(
            '*/kaufland_template/edit',
            [
                'wizard' => $this->getRequest()->getParam('wizard'),
                'nick' => $nick,
                'close_on_save' => 1,
            ]
        );
    }

    private function getRecommendedConditionValues(): array
    {
        return [
            [
                'value' => \M2E\Kaufland\Model\Listing::CONDITION_NEW,
                'label' => $this->__('New'),
            ],
            [
                'value' => \M2E\Kaufland\Model\Listing::CONDITION_USED_GOOD,
                'label' => $this->__('Used Good'),
            ],
            [
                'value' => \M2E\Kaufland\Model\Listing::CONDITION_USED_AS_NEW,
                'label' => $this->__('Used as New'),
            ],
            [
                'value' => \M2E\Kaufland\Model\Listing::CONDITION_USED_VERY_GOOD,
                'label' => $this->__('Used Very Good'),
            ],
            [
                'value' => \M2E\Kaufland\Model\Listing::CONDITION_USED_ACCEPTABLE,
                'label' => $this->__('Used Acceptable'),
            ],
        ];
    }
}
