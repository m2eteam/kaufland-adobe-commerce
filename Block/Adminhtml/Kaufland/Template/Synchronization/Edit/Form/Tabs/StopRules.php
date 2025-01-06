<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Synchronization\Edit\Form\Tabs;

use M2E\Kaufland\Model\Template\Synchronization as TemplateSynchronization;

class StopRules extends AbstractTab
{
    /** @var \M2E\Kaufland\Model\Template\Synchronization\Builder */
    private TemplateSynchronization\Builder $synchronizationBuilder;
    private \M2E\Kaufland\Model\Kaufland\Magento\Product\RuleFactory $productRuleFactory;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Magento\Product\RuleFactory $productRuleFactory,
        \M2E\Kaufland\Model\Template\Synchronization\Builder $synchronizationBuilder,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct(
            $globalDataHelper,
            $context,
            $registry,
            $formFactory,
            $data
        );
        $this->synchronizationBuilder = $synchronizationBuilder;
        $this->productRuleFactory = $productRuleFactory;
    }

    protected function _prepareForm()
    {
        $default = $this->synchronizationBuilder->getDefaultData();
        $formData = $this->getFormData();

        $formData = array_merge($default, $formData);

        $form = $this->_formFactory->create();

        $form->addField(
            'kaufland_template_synchronization_form_data_stop',
            self::HELP_BLOCK,
            [
                'content' => __(
                    'Set the Conditions when M2E Kaufland should stop Listings on Kaufland.<br/><br/>
                    If all Conditions are set to No or No Action then no Kaufland Items using this Synchronization
                    Policy will be Stopped.
                    If all Options are enabled, then an Item will be Stopped if at least one of the Stop
                    Conditions is met.<br/><br/>
                    More detailed information about ability to work with this Page you can find
                    <a href="%url" target="_blank" class="external-link">here.</a>',
                    ['url' => 'https://docs-m2.m2epro.com/stop-rules-for-kaufland-listings'],
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_kaufland_template_synchronization_form_data_stop_filters',
            [
                'legend' => __('General'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'stop_mode',
            self::SELECT,
            [
                'name' => 'synchronization[stop_mode]',
                'label' => __('Stop Action'),
                'value' => $formData['stop_mode'],
                'values' => [
                    0 => __('Disabled'),
                    1 => __('Enabled'),
                ],
                'tooltip' => __(
                    'Enable to automatically stop the Item(s) when the Stop Conditions are met.'
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_kaufland_template_synchronization_stop_rules',
            [
                'legend' => __('Stop Conditions'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'stop_status_disabled',
            self::SELECT,
            [
                'name' => 'synchronization[stop_status_disabled]',
                'label' => __('Stop When Status Disabled'),
                'value' => $formData['stop_status_disabled'],
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'tooltip' => __(
                    'Automatically stops an Item that is on Kaufland if Status is changed to \'Disabled\' in Magento.'
                ),
            ]
        );

        $fieldset->addField(
            'stop_out_off_stock',
            self::SELECT,
            [
                'name' => 'synchronization[stop_out_off_stock]',
                'label' => __('Stop When Out Of Stock'),
                'value' => $formData['stop_out_off_stock'],
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'tooltip' => __(
                    'Automatically stops an Item that is on Kaufland if Stock Availability is changed
                    to \'Out of Stock\' in Magento.'
                ),
            ]
        );

        $form->addField(
            'stop_qty_calculated_confirmation_popup_template',
            self::CUSTOM_CONTAINER,
            [
                'text' => __(
                    'Disabling this option might affect actual product data updates.
Please read <a href="%url" target="_blank">this article</a> before disabling the option.',
                    ['url' => 'https://help.m2epro.com/support/solutions/articles/9000199813'],
                ),
                'style' => 'display: none;',
            ]
        );

        $fieldset->addField(
            'stop_qty_calculated',
            self::SELECT,
            [
                'name' => 'synchronization[stop_qty_calculated]',
                'label' => __('Stop When Quantity Is'),
                'value' => $formData['stop_qty_calculated'],
                'values' => [
                    TemplateSynchronization::QTY_MODE_NONE => __('No Action'),
                    TemplateSynchronization::QTY_MODE_YES => __('Less or Equal'),
                ],
                'tooltip' => __(
                    'Automatically stops an Item on Kaufland if Quantity according to the
                     Selling Policy is changed <b>and</b> it meets the selected Conditions.'
                ),
            ]
        )->setAfterElementHtml(
            <<<HTML
<input name="synchronization[stop_qty_calculated_value]" id="stop_qty_calculated_value"
       value="{$formData['stop_qty_calculated_value']}" type="text"
       style="width: 72px; margin-left: 10px;"
       class="input-text admin__control-text required-entry validate-digits _required" />
HTML
        );

        $fieldset = $form->addFieldset(
            'magento_block_kaufland_template_synchronization_stop_advanced_filters',
            [
                'legend' => __('Advanced Conditions'),
                'collapsable' => false,
                'tooltip' => __(
                    '<p>Define Magento Attribute value(s) based on which a product must be stopped on the Channel.<br>
                    Once at least one Stop or Advanced Condition is met, the product will be stopped.</p>'
                ),
            ]
        );

        $fieldset->addField(
            'stop_advanced_rules_filters_warning',
            self::MESSAGES,
            [
                'messages' => [
                    [
                        'type' => \Magento\Framework\Message\MessageInterface::TYPE_WARNING,
                        'content' => __(
                            'Please be very thoughtful before enabling this option as this functionality
                        can have a negative impact on the Performance of your system.<br> It can decrease the speed
                        of running in case you have a lot of Products with the high number of changes made to them.'
                        ),
                    ],
                ],
            ]
        );

        $fieldset->addField(
            'stop_advanced_rules_mode',
            self::SELECT,
            [
                'name' => 'synchronization[stop_advanced_rules_mode]',
                'label' => __('Stop When Meet'),
                'value' => $formData['stop_advanced_rules_mode'],
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
            ]
        );

        $ruleModel = $this->productRuleFactory->create()->setData(
            ['prefix' => TemplateSynchronization::STOP_ADVANCED_RULES_PREFIX]
        );

        if (!empty($formData['stop_advanced_rules_filters'])) {
            $ruleModel->loadFromSerialized($formData['stop_advanced_rules_filters']);
        }

        $ruleBlock = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Product\Rule::class)
                          ->setData(['rule_model' => $ruleModel]);

        $fieldset->addField(
            'advanced_filter',
            self::CUSTOM_CONTAINER,
            [
                'container_id' => 'stop_advanced_rules_filters_container',
                'label' => __('Conditions'),
                'text' => $ruleBlock->toHtml(),
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
