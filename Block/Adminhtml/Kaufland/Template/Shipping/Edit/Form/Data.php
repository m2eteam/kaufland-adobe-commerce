<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Shipping\Edit\Form;

use M2E\Kaufland\Model\Template\Shipping;

class Data extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;
    private \M2E\Kaufland\Model\Warehouse\Repository $warehouseRepository;
    private \M2E\Kaufland\Model\ShippingGroup\Repository $shippingGroupRepository;
    private \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper;

    public function __construct(
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        \M2E\Kaufland\Model\Warehouse\Repository $warehouseRepository,
        \M2E\Kaufland\Model\ShippingGroup\Repository $shippingGroupRepository,
        array $data = []
    ) {
        $this->globalDataHelper = $globalDataHelper;
        $this->storefrontRepository = $storefrontRepository;
        $this->warehouseRepository = $warehouseRepository;
        $this->shippingGroupRepository = $shippingGroupRepository;
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm(): Data
    {
        $formData = $this->getFormData();
        $default = $this->getDefault();
        $formData = array_merge($default, $formData);

        if (isset($formData['storefront_id'])) {
            $storefrontId = (int) $formData['storefront_id'];
        } else {
            $storefrontId = (int) $this->getRequest()->getParam('storefront_id');
        }

        $form = $this->_formFactory->create();

        $form->addField(
            'shipping_id',
            'hidden',
            [
                'name' => 'shipping[id]',
                'value' => $formData['id'] ?? '',
            ]
        );

        $form->addField(
            'shipping_title',
            'hidden',
            [
                'name' => 'shipping[title]',
                'value' => $this->getTitle(),
            ]
        );

        $form->addField(
            'storefront_id_hidden',
            'hidden',
            [
                'name' => 'shipping[storefront_id]',
                'value' => $storefrontId,
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_shipping_edit_form',
            [
                'legend' => __('Shipping'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'handling_time',
            'hidden',
            [
                'name' => 'shipping[handling_time]',
                'value' => $formData['handling_time'],
            ]
        );

        $fieldset->addField(
            'handling_time_attribute',
            'hidden',
            [
                'name' => 'shipping[handling_time_attribute]',
                'value' => $formData['handling_time_attribute'],
            ]
        );

        $handlingModeOptions = $this->getHandlingTimeOptions();
        $handlingModeOptions[] = $this->getAttributesOptions(
            Shipping::HANDLING_TIME_MODE_ATTRIBUTE,
            $formData['handling_time_mode'],
            $formData['handling_time_attribute'] ?? ''
        );

        $fieldset->addField(
            'handling_time_mode',
            self::SELECT,
            [
                'name' => 'shipping[handling_time_mode]',
                'label' => __('Handling Time'),
                'title' => __('Handling Time'),
                'values' => $handlingModeOptions,
                'create_magento_attribute' => true,
                'class' => 'admin__control-select Kaufland-validate-handling-time-mode',
                'tooltip' => __(
                    'The number of working days till the order is handed over to the carrier.'
                ),
                'required' => true,
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text,select');

        $fieldset->addField(
            'storefront_id',
            self::SELECT,
            [
                'name' => 'shipping[storefront_id]',
                'label' => $this->__('Storefront'),
                'values' => $this->getStorefrontDataOptions(),
                'value' => $storefrontId,
                'disabled' => $storefrontId != 0,
                'required' => true,
            ]
        );

        $buttonRefreshWarehouses = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)->addData(
            [
                'id' => 'refresh_warehouse',
                'label' => $this->__('Refresh Warehouses'),
                'onclick' => 'KauflandTemplateShippingObj.refreshWarehouses()',
                'class' => 'action-primary',
                'style' => 'margin-left: 70px;',
            ]
        );

        $fieldset->addField(
            'warehouse_id',
            self::SELECT,
            [
                'name' => 'shipping[warehouse_id]',
                'label' => $this->__('Warehouse'),
                'values' => $this->getWarehouseDataOptions(),
                'value' => $formData['warehouse_id'] ?? '',
                'after_element_html' => $buttonRefreshWarehouses->toHtml(),
                'required' => true,
            ]
        );

        $buttonRefreshShippingGroups = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)->addData(
            [
                'id' => 'refresh_shipping_group',
                'label' => $this->__('Refresh Shipping Group'),
                'onclick' => 'KauflandTemplateShippingObj.refreshShippingGroups()',
                'class' => 'action-primary',
                'style' => 'margin-left: 70px;',
            ]
        );

        $fieldset->addField(
            'shipping_group_id',
            self::SELECT,
            [
                'name' => 'shipping[shipping_group_id]',
                'label' => $this->__('Shipping Group'),
                'values' => $storefrontId
                    ? $this->getShippingGroupDataOptionsByStorefront($storefrontId)
                    : $this->getShippingGroupDataOptions(),
                'value' => $formData['shipping_group_id'] ?? '',
                'required' => true,
                'after_element_html' => $buttonRefreshShippingGroups->toHtml(),
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function getTitle()
    {
        $template = $this->globalDataHelper->getValue('kaufland_template_shipping');

        if ($template === null) {
            return '';
        }

        return $template->getTitle();
    }

    private function getFormData()
    {
        $template = $this->globalDataHelper->getValue('kaufland_template_shipping');

        if ($template === null || $template->getId() === null) {
            return [];
        }

        return $template->getData();
    }

    private function getDefault()
    {
        return $this->modelFactory->getObject('Template_Shipping_Builder')->getDefaultData();
    }

    public function getStorefrontDataOptions(): array
    {
        $storefronts = $this->storefrontRepository->getAll();

        $optionsResult = [];
        foreach ($storefronts as $storefront) {
            $optionsResult[] = [
                'value' => $storefront['id'],
                'label' => $storefront->getTitle()
            ];
        }

        return $optionsResult;
    }

    public function getWarehouseDataOptions(): array
    {
        $optionsResult = [];
        foreach ($this->warehouseRepository->getAll() as $warehouse) {
            $optionsResult[] = [
                'value' => $warehouse['id'],
                'label' => $warehouse->getName()
            ];
        }

        return $optionsResult;
    }

    public function getShippingGroupDataOptionsByStorefront(int $storefrontId): ?array
    {
        $shippingGroups = $this->shippingGroupRepository->findByStorefrontId($storefrontId);

        $optionsResult = [];
        foreach ($shippingGroups as $shippingGroup) {
            $optionsResult[] = [
                'value' => $shippingGroup['id'],
                'label' => $shippingGroup->getName()
            ];
        }

        return $optionsResult;
    }

    public function getShippingGroupDataOptions(): array
    {
        $shippingGroups = $this->shippingGroupRepository->getAll();

        $optionsResult = [];
        foreach ($shippingGroups as $shippingGroup) {
            $optionsResult[] = [
                'value' => $shippingGroup['id'],
                'label' => $shippingGroup->getName()
            ];
        }

        return $optionsResult;
    }

    public function getHandlingTimeOptions(): array
    {
        $formData = $this->getFormData();
        $default = $this->getDefault();
        $formData = array_merge($default, $formData);
        $options = [
            ['value' => '', 'label' => '', 'attrs' => ['class' => 'empty']],
        ];

        $handlingOptions = [
            [
                "handling_time_value" => "",
                "title" => "Not Set"
            ],
            [
                "handling_time_value" => "0",
                "title" => "Same Business Day"
            ],

        ];

        $days = [1, 2, 3, 4, 5, 6, 7, 10, 15, 20, 30, 40];

        if (!empty($formData['handling_time']) && !in_array((int)$formData['handling_time'], $days, true)) {
            $days[] = (int)$formData['handling_time'];
            sort($days);
        }

        foreach ($days as $day) {
            $handlingOptions[] = [
                "handling_time_value" => (string)$day,
                "title" => $day . " Business Day" . ($day > 1 ? "s" : "")
            ];
        }

        foreach ($handlingOptions as $handlingOption) {
            $label = (string)__($handlingOption['title']);

            $tmpOption = [
                'value' => Shipping::HANDLING_TIME_MODE_VALUE,
                'label' => $label,
                'attrs' => ['attribute_code' => $handlingOption['handling_time_value']],
            ];

            if (
                $formData['handling_time_mode'] == Shipping::HANDLING_TIME_MODE_VALUE &&
                $handlingOption['handling_time_value'] == $formData['handling_time']
            ) {
                $tmpOption['attrs']['selected'] = 'selected';
            }

            $options[] = $tmpOption;
        }

        return $options;
    }

    /**
     * @param int $attributeValue
     * @param int $handlingTimeMode
     * @param string $handlingTimeAttribute
     *
     * @return array
     */
    public function getAttributesOptions(
        int $attributeValue,
        int $handlingTimeMode,
        string $handlingTimeAttribute
    ): array {
        $options = [
            'value' => [],
            'label' => __('Magento Attribute'),
            'attrs' => ['is_magento_attribute' => true],
        ];

        foreach ($this->magentoAttributeHelper->getAll() as $attribute) {
            $tmpOption = [
                'value' => $attributeValue,
                'label' => ($attribute['label']),
                'attrs' => ['attribute_code' => $attribute['code']],
            ];

            if (
                $handlingTimeMode === Shipping::HANDLING_TIME_MODE_ATTRIBUTE
                && $attribute['code'] === $handlingTimeAttribute
            ) {
                $tmpOption['attrs']['selected'] = 'selected';
            }

            $options['value'][] = $tmpOption;
        }

        return $options;
    }

    protected function _toHtml()
    {
        $this->jsUrl->addUrls(
            [
                'kaufland_template/refreshWarehouses' => $this->getUrl(
                    '*/kaufland_template/refreshWarehouses',
                    [

                    ]
                ),
                'kaufland_template/refreshShippingGroups' => $this->getUrl(
                    '*/kaufland_template/refreshShippingGroups',
                    [

                    ]
                ),
                'kaufland_template/getShippingGroupsByStorefront' => $this->getUrl(
                    '*/kaufland_template/getShippingGroupsByStorefront',
                    [

                    ]
                ),
            ]
        );

        $this->jsPhp->addConstants(
            [
                '\M2E\Kaufland\Model\Template\Shipping::HANDLING_TIME_MODE_VALUE' => Shipping::HANDLING_TIME_MODE_VALUE,
                '\M2E\Kaufland\Model\Template\Shipping::HANDLING_TIME_MODE_ATTRIBUTE' => Shipping::HANDLING_TIME_MODE_ATTRIBUTE,
            ]
        );

        $this->js->add(
            <<<JS
    require([
        'Kaufland/Kaufland/Template/Shipping'
        ], function() {
    window.KauflandTemplateShippingObj = new KauflandTemplateShipping();
    KauflandTemplateShippingObj.initObservers();
    });
JS
        );
        return parent::_toHtml();
    }
}
