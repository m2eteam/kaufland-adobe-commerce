<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\View\Edit;

use M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Chooser\Specific\Form as AttributesForm;
use M2E\Kaufland\Model\Category\Dictionary;

class Form extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\DictionaryMapper $dictionaryMapper;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\DictionaryMapper $dictionaryMapper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->dictionaryMapper = $dictionaryMapper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/saveCategoryAttributes'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\View\Edit $parentBlock */
        $parentBlock = $this->getParentBlock();
        $dictionary = $parentBlock->getDictionary();

        $form->addField(
            'dictionary_id',
            'hidden',
            [
                'name' => 'dictionary_id',
                'value' => $dictionary->getId(),
            ]
        );

        $fieldset = $form->addFieldset(
            'attributes',
            [
                'legend' => __('Product Attributes'),
                'collapsable' => false,
            ]
        );

        $realAttributes = $this->dictionaryMapper->getProductAttributes($dictionary);
        if ($realAttributes !== []) {
            $this->addAttributesTable($fieldset, 'real_attributes', $realAttributes);
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        $this->jsTranslator->add(
            'Item Specifics cannot have the same Labels.',
            'Item Specifics cannot have the same Labels.'
        );

        $this->jsPhp->addConstants(
            [
                '\M2E\Kaufland\Model\Category\Dictionary::VALUE_MODE_KAUFLAND_RECOMMENDED' =>
                    Dictionary::VALUE_MODE_KAUFLAND_RECOMMENDED,
                '\M2E\Kaufland\Model\Category\Dictionary::VALUE_MODE_CUSTOM_VALUE' =>
                    Dictionary::VALUE_MODE_CUSTOM_VALUE,
                '\M2E\Kaufland\Model\Category\Dictionary::VALUE_MODE_CUSTOM_ATTRIBUTE' =>
                    Dictionary::VALUE_MODE_CUSTOM_ATTRIBUTE,
                '\M2E\Kaufland\Model\Category\Dictionary::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE' =>
                    Dictionary::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE,
            ]
        );

        $this->js->addRequireJs(
            [
                'etcs' => 'Kaufland/Kaufland/Template/Category/Specifics',
            ],
            <<<JS
        window.KauflandTemplateCategorySpecificsObj = new KauflandTemplateCategorySpecifics();
JS
        );

        return parent::_prepareLayout();
    }

    private function addAttributesTable(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        string $id,
        array $attributes
    ): void {
        /** @var AttributesForm\Renderer\Dictionary $renderer */
        $renderer = $this->getLayout()->createBlock(AttributesForm\Renderer\Dictionary::class);

        $config = [
            'specifics' => $attributes,
        ];

        $field = $fieldset->addField($id, AttributesForm\Element\Dictionary::class, $config);
        $field->setRenderer($renderer);
    }
}
