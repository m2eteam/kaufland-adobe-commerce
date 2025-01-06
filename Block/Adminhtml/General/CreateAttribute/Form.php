<?php

namespace M2E\Kaufland\Block\Adminhtml\General\CreateAttribute;

use M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm;
use M2E\Kaufland\Model\Magento\Attribute\Builder as AttributeBuilder;

class Form extends AbstractForm
{
    protected $handlerId;

    protected $allowedTypes = [];
    protected $applyToAllAttributeSets = true;

    /** @var \M2E\Core\Helper\Magento\AttributeSet */
    protected $magentoAttributeSetHelper;

    /** @var \M2E\Core\Helper\Data */
    private $dataHelper;

    public function __construct(
        \M2E\Core\Helper\Magento\AttributeSet $magentoAttributeSetHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Core\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->magentoAttributeSetHelper = $magentoAttributeSetHelper;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $this->handlerId($this->getParentBlock()->getData('handler_id'));
        $this->allowedTypes($this->getParentBlock()->getData('allowed_types'));
        $this->applyToAll($this->getParentBlock()->getData('apply_to_all'));

        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
            ],
        ]);

        $text = __('This Tool allows you to quickly');
        $create = __('Create');
        $text2 = __('a new');
        $text3 = __('Magento Attribute');
        $note = __('Note:');
        $text4 = __('for the selected Option. In order to Create an Attribute, you have to fill in the Attribute Label,
                Attribute Code, Catalog Input Type, Scope, Default Value and Attribute Sets fields.');
        $text5 = __('Some of the fields may not be available for selection. The availability
                depends on the Option for which the Attribute is being created.');
        $test6 = __('This Option does not imply automatic Product Attribute Value set up.
                After the Attribute
                becomes available in Magento, you should Manually provide the Value for the Product.');

        $form->addField(
            'create_attribute_help_block',
            self::HELP_BLOCK,
            [
                'content' =>
                    $text . '<strong>' . ' ' . $create . '</strong>' . $text2 . '<strong>' . ' ' .  $text3 . '</strong>' .
                    ' ' . $text4 . '<br/><br/>' .  '<strong>' . $note . ' ' . '</strong>' . $text5 . '<br/>' .
                    '<strong>' . $note . ' ' . '</strong>' . $test6,
            ]
        );

        $fieldset = $form->addFieldset('magento_create_custom_attribute', [
            'legend' => ' ',
            'collapsable' => false,
        ]);

        $fieldset->addField(
            'store_label',
            'text',
            [
                'name' => 'store_label',
                'label' => __('Default Label'),
                'required' => true,
            ]
        );

        $classes = 'validate-length maximum-length-30 Kaufland-validate-attribute-code ';
        $classes .= 'Kaufland-validate-attribute-code-to-be-unique';

        $fieldset->addField(
            'code',
            'text',
            [
                'name' => 'code',
                'label' => __('Attribute Code'),
                'class' => $classes,
                'required' => true,
            ]
        );

        $inputTypes = [];
        foreach ($this->allowedTypes() as $type) {
            $inputTypes[] = [
                'value' => $type,
                'label' => $this->getTitleByType($type),
            ];
        }

        $fieldset->addField(
            'input_type_select',
            self::SELECT,
            [
                'name' => 'input_type',
                'label' => __('Catalog Input Type'),
                'values' => $inputTypes,
                'value' => '',
                'disabled' => $this->isOneOnlyTypeAllowed(),
            ]
        );

        if ($this->isOneOnlyTypeAllowed()) {
            $fieldset->addField(
                'input_type',
                'hidden',
                [
                    'name' => 'input_type',
                    'value' => $this->allowedTypes()[0],
                ]
            );
        }

        $fieldset->addField(
            'scope',
            self::SELECT,
            [
                'name' => 'scope',
                'label' => __('Scope'),
                'values' => [
                    [
                        'value' => AttributeBuilder::SCOPE_STORE,
                        'label' => __('Store View'),
                    ],
                    [
                        'value' => AttributeBuilder::SCOPE_WEBSITE,
                        'label' => __('Website'),
                    ],
                    [
                        'value' => AttributeBuilder::SCOPE_GLOBAL,
                        'label' => __('Global'),
                    ],

                ],
                'value' => '',
            ]
        );

        $fieldset->addField(
            'default_value',
            'text',
            [
                'name' => 'default_value',
                'label' => __('Default Value'),
            ]
        );

        $attributeSets = [];
        $values = [];
        foreach ($this->magentoAttributeSetHelper->getAll() as $item) {
            $attributeSets[] = [
                'value' => $item['attribute_set_id'],
                'label' => $item['attribute_set_name'],
            ];
            $values[] = $item['attribute_set_id'];
        }

        $fieldset->addField(
            'attribute_sets_multiselect',
            'multiselect',
            [
                'name' => 'attribute_sets[]',
                'label' => __('Attribute Sets'),
                'values' => $attributeSets,
                'value' => $values,
                'required' => true,
                'style' => 'width: 70%',
                'field_extra_attributes' => $this->applyToAll() ? 'style="display: none;"' : '',
            ]
        );

        if ($this->applyToAll()) {
            $fieldset->addField(
                'attribute_sets_multiselect_note',
                'note',
                [
                    'label' => __('Attribute Sets'),
                    'text' => '<strong>' . __('Will be added to the all Attribute Sets.')
                        . '</strong>',
                ]
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }

    //########################################

    protected function _toHtml()
    {
        $this->jsTranslator->addTranslations([
            'Invalid attribute code' => __(
                'Please use only letters (a-z),
                numbers (0-9) or underscore(_) in this field, first character should be a letter.'
            ),
            'Attribute with the same code already exists' => __('Attribute with the same code already exists.'),
            'Attribute has been created.' => __('Attribute has been created.'),
            'Please enter a valid date.' => __('Please enter a valid date.'),
        ]);

        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Magento\Attribute\Builder::class)
        );

        $this->jsUrl->addUrls([
            'general/generateAttributeCodeByLabel' => $this->getUrl('general/generateAttributeCodeByLabel'),
            'general/isAttributeCodeUnique' => $this->getUrl('general/isAttributeCodeUnique'),
            'general/createAttribute' => $this->getUrl('general/createAttribute'),
        ]);

        $this->js->addRequireJs(
            ['jQuery' => 'jquery'],
            <<<JS

        var handler = window['{$this->handlerId()}'];

        jQuery.validator.addMethod('Kaufland-validate-attribute-code', function(value, element) {
            return handler.validateAttributeCode(value, element);
        }, Kaufland.translator.translate('Invalid attribute code'));

        jQuery.validator.addMethod('Kaufland-validate-attribute-code-to-be-unique', function(value, element) {
            return handler.validateAttributeCodeToBeUnique(value, element);
        }, Kaufland.translator.translate('Attribute with the same code already exists'));
JS
        );

        return parent::_toHtml();
    }

    //########################################

    public function handlerId($value = null)
    {
        if ($value === null) {
            return $this->handlerId;
        }

        $this->handlerId = $value;

        return $this->handlerId;
    }

    public function applyToAll($value = null)
    {
        if ($value === null) {
            return $this->applyToAllAttributeSets;
        }

        $this->applyToAllAttributeSets = $value;

        return $this->applyToAllAttributeSets;
    }

    public function allowedTypes($value = null)
    {
        if ($value === null) {
            return count($this->allowedTypes) ? $this->allowedTypes : $this->getAllAvailableTypes();
        }

        $this->allowedTypes = $value;

        return $this->allowedTypes;
    }

    // ---------------------------------------

    public function getTitleByType($type)
    {
        $titles = [
            AttributeBuilder::TYPE_TEXT => __('Text Field'),
            AttributeBuilder::TYPE_TEXTAREA => __('Text Area'),
            AttributeBuilder::TYPE_PRICE => __('Price'),
            AttributeBuilder::TYPE_SELECT => __('Select'),
            AttributeBuilder::TYPE_MULTIPLE_SELECT => __('Multiple Select'),
            AttributeBuilder::TYPE_DATE => __('Date'),
            AttributeBuilder::TYPE_BOOLEAN => __('Yes/No'),
        ];

        return isset($titles[$type]) ? $titles[$type] : __('N/A');
    }

    public function getAllAvailableTypes()
    {
        return [
            AttributeBuilder::TYPE_TEXT,
            AttributeBuilder::TYPE_TEXTAREA,
            AttributeBuilder::TYPE_PRICE,
            AttributeBuilder::TYPE_SELECT,
            AttributeBuilder::TYPE_MULTIPLE_SELECT,
            AttributeBuilder::TYPE_DATE,
            AttributeBuilder::TYPE_BOOLEAN,
        ];
    }

    public function isOneOnlyTypeAllowed()
    {
        return count($this->allowedTypes()) == 1;
    }

    //########################################
}
